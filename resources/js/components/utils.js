let check = function (type, users, money) {
    console.log(type,users,money)
    switch (type) {
        case '批发':
            if (users < 20 && money < 5000) {
                return true;
            }
            break;
        case '零售':
            if (users < 50 && money < 500) {
                return true;
            }
            break;
        case '商业综合体管理':
            if (users < 100 && money < 8000) {
                return true;
            }
            break;
        case '市场管理':
            if (users < 100 && money < 2000) {
                return true;
            }
            break;
        case '家庭服务':
            if (users < 100) {
                return true;
            }
            break;
        case '住宿':
            if (users < 100 && money < 2000) {
                return true;
            }
            break;
        case '餐饮':
            if (users < 100 && money < 2000) {
                return true;
            }
            break;
        case '文化体育':
            if (users < 100) {
                return true;
            }
            break;
        case '交通运输':
            if (users < 300 && money < 3000) {
                return true;
            }
            break;
        case '仓储':
            if (users < 100 && money < 1000) {
                return true;
            }
            break;
        case '邮政':
            if (users < 300 && money < 2000) {
                return true;
            }
            break;
        case '会议展览':
            if (users < 100) {
                return true;
            }
            break;
        case '教育培训':
            if (users < 100) {
                return true;
            }
            break;
        default:
            return false;
    }
    return false;
}

const companyField = function (field) {
    let key = {
        title: '单位名称',
        slug: '纳税人识别号',
        operator: '经办人',
        phone: '联系电话',
        lg_class: '行业分类',
        sm_class: '所属行业',
        start_at: '复工时间',
        users: '员工数',
        ye_shouru: '营业收入',
        total_money: '资产总额',
        zz_shui: '增值税',
        sd_shui: '所得税',
        zz_sd_total: '所得税总和',
        two_zz: '开工后次月起两个月增值税合计',
        bank_type: '开户银行',
        bank_num: '户银行账号',
    }
    return key;
}
export default {
    check,
    companyField,
}
