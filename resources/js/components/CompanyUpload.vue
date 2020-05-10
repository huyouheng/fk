<template>
    <i-form ref="formCustom" :label-width="100" label-position="left">
        <form-item :label="title">
            <Upload
                multiple
                type="drag"
                name="fk-files"
                :headers="getHeaders"
                :on-format-error="formatError"
                :on-remove="removeFile"
                :format="['pdf', 'word', 'doc', 'docx', 'png','jpg','jpeg']"
                :default-file-list="file"
                :on-preview="downloadFile"
                action="/manager/upload">
                <div style="padding: 0 0">
                    <Icon type="ios-cloud-upload" size="52" style="color: #3399ff"></Icon>
                    <span>点击或拖拽上传文件</span>
                </div>
            </Upload>
        </form-item>
    </i-form>
</template>

<script>
    export default {
        props: ['title', 'name', 'cid', 'files'],
        name: "CompanyUpload",
        data(){
            return {
                file: [],
            }
        },
        computed: {
            getHeaders() {
                let token = document.head.querySelector('meta[name="csrf-token"]');
                return {"X-CSRF-TOKEN": token.content, "name": this.name, 'cid': this.cid};
            }
        },
        created() {
            if (this.files) {
                this.file = JSON.parse(this.files)
            }
        },
        methods: {
            formatError() {
                return this.$Notice.error({
                    desc: '文件格式错误，仅仅支持pdf, word, doc, docx, png,jpg,jpeg!',
                    duration: 6
                });
            },
            removeFile(file){
                console.log(file)
                axios.put('/manager/upload/'+this.cid, {'name': this.name, file: file.store}).then(res=>{
                    let data =res.data;
                    console.log(data)
                })
            },
            downloadFile(file){
                console.log(file)
            },
        }
    }
</script>

<style scoped>

</style>
