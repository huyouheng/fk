<style>
    .ivu-form-item-label {
        text-align: center !important;
        line-height: 20px !important;
    }
    .ivu-notice {
        z-index: 9999 !important;
    }
</style>
<template>
    <i-form :label-width="120">
        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*单位名称">
                    <Input disabled v-model="company.title" placeholder="Enter something..."></Input>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*纳税人识别号">
                    <Input disabled v-model="company.slug" placeholder="Enter something..."></Input>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*经办人">
                    <Input v-model="company.operator" placeholder="请填写经办人"
                           v-on:on-blur="itemChange('operator', '经办人')"></Input>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*联系电话">
                    <Input v-model="company.phone" placeholder="请填写联系电话"
                           v-on:on-blur="itemChange('phone', '联系电话')"></Input>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*行业分类">
                    <Select v-model="company.lg_class" placeholder="请选择分类" v-on:on-change="industryChange">
                        <Option :value="item.name" :key="item.id" v-for="item in industry">{{item.name}}</Option>
                    </Select>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*所属行业">
                    <Select v-model="company.sm_class" placeholder="请选择所属行业" v-on:on-change="industryTypeChange">
                        <Option :value="item.name" :key="item.id" v-for="item in industryType">{{item.name}}</Option>
                    </Select>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019年年末职工人数">
                    <InputNumber v-model="company.users" :step="1" style="width: 100%;" :min="0" placeholder="请填写职工人数"
                                 v-on:on-blur="itemChange('users', '职工人数')"></InputNumber>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*复工时间">
                    <DatePicker type="date" :value="company.start_at" v-on:on-change="startAt" placeholder="请选择复工时间"
                                style="width: 100%"></DatePicker>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019年营业收入（万元）">
                    <InputNumber v-model="company.ye_shouru" style="width: 100%;" placeholder="请填写2019年营业收入"
                                 v-on:on-blur="itemChange('ye_shouru', '2019年营业收入')"></InputNumber>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019年年末资产总额（万元）">
                    <InputNumber v-model="company.total_money" style="width: 100%;" placeholder="请填写2019年年末资产总额"
                                 v-on:on-blur="itemChange('total_money', '2019年年末资产总额')"></InputNumber>
                </FormItem>
            </Col>
        </Row>


        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019年增值税（万元）">
                    <InputNumber v-model="company.zz_shui" style="width: 100%;" placeholder="请填写2019年增值税"
                                 v-on:on-blur="itemChange('zz_shui', '2019年增值税')"></InputNumber>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019企业所得税（万元）">
                    <InputNumber v-model="company.sd_shui" style="width: 100%;" placeholder="请填写2019企业所得税"
                                 v-on:on-blur="itemChange('sd_shui', '2019企业所得税')"></InputNumber>
                </FormItem>
            </Col>
        </Row>


        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*2019年增值税与企业所得税合计（万元）">
                    <InputNumber v-model="company.zz_sd_total" style="width: 100%;" placeholder="请填写2019年增值税与企业所得税合计"
                                 v-on:on-blur="itemChange('zz_sd_total', '2019年增值税与企业所得税合计')"></InputNumber>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*开工后次月起两个月增值税合计（万元）">
                    <InputNumber v-model="company.two_zz" style="width: 100%;" placeholder="请填写开工后次月起两个月增值税合计"
                                 v-on:on-blur="itemChange('two_zz', '开工后次月起两个月增值税合计')"></InputNumber>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*开户银行及户名">
                    <Input v-model="company.bank_type" placeholder="请填写开户银行及户名"
                           v-on:on-blur="itemChange('bank_type', '开户银行及户名')"></Input>
                </FormItem>
            </Col>
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="*银行账号">
                    <Input v-model="company.bank_num" placeholder="请填写银行账号"
                           v-on:on-blur="itemChange('bank_num', '银行账号')"></Input>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16" v-if="showMoney">
            <Col :xs="24" :sm="24" :md="12" :lg="12">
                <FormItem label="补助金额(万元)">
                    <Input v-model="company.money" placeholder="请填写银行账号"
                           v-on:on-blur="itemChange('bank_num', '银行账号')"></Input>
                </FormItem>
            </Col>
        </Row>

        <Row :gutter="16">
            <span style="color: red;margin-left: 10px;">注: 带 <b>*</b> 为必填项</span>
        </Row>
    </i-form>
</template>

<script>
    export default {
        name: "CompanyInfo",
        props: ['companies', 'industries'],
        data() {
            return {
                company: {
                    title: '',
                    slug: '',
                    operator: '',
                    phone: '',
                    users: '',
                    start_at: '',
                    total_money: '',
                    ye_shouru: '',
                    zz_shui: '',
                    sd_shui: '',
                    zz_sd_total: '',
                    two_zz: '',
                    bank_type: '',
                    bank_num: '',
                    sm_class: '',
                    lg_class: '',
                },
                industry: [],
                industryType: [],
                showMoney: false,
            }
        },
        created() {
            this.company = JSON.parse(this.companies)
            if (this.company.users) {
                this.company.users = Number.parseInt(this.company.users)
            }
            if (this.company.ye_shouru) {
                this.company.ye_shouru = Number.parseFloat(this.company.ye_shouru)
            }
            if (this.company.total_money) {
                this.company.total_money = Number.parseFloat(this.company.total_money)
            }
            if (this.company.zz_shui) {
                this.company.zz_shui = Number.parseFloat(this.company.zz_shui)
            }
            if (this.company.sd_shui) {
                this.company.sd_shui = Number.parseFloat(this.company.sd_shui)
            }
            if (this.company.zz_sd_total) {
                this.company.zz_sd_total = Number.parseFloat(this.company.zz_sd_total)
            }
            if (this.company.two_zz) {
                this.company.two_zz = Number.parseFloat(this.company.two_zz)
            }
            this.industry = JSON.parse(this.industries)
            if (this.company.lg_class) {
                this.initLgClass(this.company.lg_class, 'init');
            }
            if(this.company.report && this.company.report.status === 2) {
                this.showMoney = true;
            }
        },
        methods: {
            industryChange(item) {
                this.initLgClass(item, 'update');
            },
            industryTypeChange(item){
                this.updateItem('sm_class', item, '所属行业')
            },
            initLgClass(item, type='init'){
                let index = this.industry.findIndex(i => {
                    return i.name === item;
                })
                if (index !== -1) {
                    this.industryType = this.industry[index].children
                    if (type != 'init') {
                        this.updateItem('lg_class', item, '行业分类')
                        this.updateItem('sm_class', '', '')
                    }
                }
            },
            itemChange(key, title) {
                let value = this.company[key]
                if (key === 'users') {
                    console.log(value)
                    if (!Number.isInteger(value)) {
                        return   this.$Notice.error({
                            desc: '填写 ' + title + ':' + value + ' 错误',
                            duration: 5
                        });
                    }
                }
                if (value) {
                    this.updateItem(key, value, title)
                }

            },
            updateItem(key, value, title) {
                axios.put('/company/update/' + this.company.id, {key: key, value: value}).then(res => {
                    let data = res.data;
                    if (data.status) {
                        if (title) {
                            this.$Notice.success({
                                desc: '保存 ' + title + ':' + value + ' 成功',
                                duration: 5
                            });
                        }
                    }
                })
            },
            startAt(date, type) {
                this.updateItem('start_at', date, '复工时间')

            },
        },
    }
</script>

<style scoped>

</style>
