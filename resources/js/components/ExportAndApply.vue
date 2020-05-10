<style lang="less">
    .vertical-center-modal {
        display: flex;
        align-items: center;
        justify-content: center;

        .ivu-modal {
            top: 0;
        }
    }
</style>
<template>
    <div style="margin-top: 10px;margin-bottom: 20px;">
        <Divider/>
        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <Button @click="exportData" :loading="isLoading">导出商贸服务企业补助申请表</Button>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <Button @click="applyData" :loading="isApply" type="success" :disabled="applyBtnDisable">{{applyMsg}}</Button>
            </Col>
        </Row>
        <Modal
            title="提示"
            v-model="modal10"
            class-name="vertical-center-modal">
            <h3 style="padding: 10px;">{{msg}}！</h3>
            <div slot="footer" style="text-align: center;">
                <Button type="info" @click="modal10=false"> 确定 </Button>
            </div>
        </Modal>
    </div>
</template>

<script>
    import Utils from './utils';

    export default {
        name: "Export",
        props: ['company'],
        data() {
            return {
                isLoading: false,
                isApply: false,
                modal10: false,
                msg: '不符合小微企业标准，请核实',
                applyMsg: '申报',
                companies: [],
                cid: 0,
                applyBtnDisable: false,
            }
        },
        mounted(){
            this.companies = JSON.parse(this.company)
            this.cid = this.companies.id;
            console.log(this.companies)
            if (this.companies.report && this.companies.report.status === 1) {
                this.applyMsg = '申报审核中';
                this.applyBtnDisable = true;
            }
        },
        methods: {
            exportData() {
                this.isLoading = true;
                axios.post('/manager/export/', {cid: this.cid}).then(res => {
                    let data = res.data;
                    if (data.status) {
                        let path = data.data;
                        console.log(path);
                        let host = window.location.host;
                        let protocol = window.location.protocol;
                        let url = protocol + '//' + host + '/' + path;
                        window.location.href = url;
                        console.log(url)
                    }
                    this.isLoading = false;
                });
            },
            tipMsg(msg){
                this.msg = msg;
                this.modal10 = true;
                this.isApply = false;
            },
            applyData() {
                this.isApply = true;
                const _this = this;
                axios.post('/company/index/' + this.cid).then(res => {
                    let data = res.data;
                    if (data.status) {
                        let company = data.data;
                        let field = Utils.companyField();
                        for (let key in field) {
                            if (company[key] === null) {
                                _this.msg = field[key]+' 不能为空!';
                                _this.modal10 = true;
                                _this.isApply = false;
                                return false;
                            }
                        }
                        let files = company.files;
                        if (!files) {
                            return _this.tipMsg('附件不能为空');
                        }
                        if (!files.ye_copy) {
                            return _this.tipMsg('附件：企业营业执照复印件不能为空');
                        } else {
                            if (!JSON.parse(files.ye_copy).length) {
                                return _this.tipMsg('附件：企业营业执照复印件不能为空');
                            }
                        }
                        if (!files.sd_report) {
                            return _this.tipMsg('附件：2019年企业所得税年报不能为空');
                        } else {
                            if (!JSON.parse(files.sd_report).length) {
                                return _this.tipMsg('附件：2019年企业所得税年报不能为空');
                            }
                        }
                        if (!files.ns_prove) {
                            return _this.tipMsg('附件：2019年全年纳税证明不能为空');
                        } else {
                            if (!JSON.parse(files.ns_prove).length) {
                                return _this.tipMsg('附件：2019年全年纳税证明不能为空');
                            }
                        }
                        if (!files.zzs_prove) {
                            return _this.tipMsg('附件：增值税完税证明不能为空');
                        } else {
                            if (!JSON.parse(files.zzs_prove).length) {
                                return _this.tipMsg('附件：增值税完税证明不能为空');
                            }
                        }
                        //

                        console.log(files)
                        let result = Utils.check(company.sm_class, company.users, company.ye_shouru)
                        if (!result) {
                            this.modal10 = true;
                        }
                        let report = company.report;
                        if (report && report.status === 1) {
                            return _this.tipMsg('信息正在审核中...');
                        }
                        console.log(report)

                        if (report && report.time_status === 2) {
                            if (!files.cp_card) {
                                return _this.tipMsg('附件：企业公章收据不能为空');
                            } else {
                                if (!JSON.parse(files.cp_card).length) {
                                    return _this.tipMsg('附件：企业公章收据不能为空');
                                }
                            }
                        }

                        // TODO 提交申请
                        axios.post('/company/apply').then(res=>{
                            let data =res.data;
                            if (data.status) {
                                _this.applyMsg = '申报审核中';
                                _this.applyBtnDisable = true;
                            }
                            return _this.tipMsg(data.msg);
                        })

                    } else {

                    }
                    this.isApply = false;
                });
            },
        },
    }
</script>

<style scoped>

</style>
