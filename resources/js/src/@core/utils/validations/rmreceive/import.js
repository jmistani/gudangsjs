import { extend, localize } from 'vee-validate'
import {
    required as rule_required,
    email as rule_email,
    min as rule_min,
    confirmed as rule_confirmed,
    regex as rule_regex,
    between as rule_between,
    alpha as rule_alpha,
    integer as rule_integer,
    digits as rule_digits,
    alpha_dash as rule_alpha_dash,
    alpha_num as rule_alpha_num,
    length as rule_length,
} from 'vee-validate/dist/rules'
import ar from 'vee-validate/dist/locale/ar.json'
import en from 'vee-validate/dist/locale/en.json'


export const requiredFile = extend('requiredFile', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan File"
        } else if (value != null && value.size > 0) {
            return "Masukkan File"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: 'Masukkan Tanggal'
});
export const requiredHargaPenerimaan = extend('requiredHargaPenerimaan', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan Harga Penerimaan"
        } else if (isNaN(value) == true || parseFloat(value) == 'NaN' || value <= 0) {
            return "Masukkan Harga Penerimaan yang benar"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: 'Masukkan Tanggal'
});

export const requiredDate = extend('requiredDate', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan tanggal"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: 'Masukkan Tanggal'
});


export const requiredJumlah = extend('requiredJumlah', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan tanggal"
        }
        if (parseFloat(value) != 'NaN') {
            return true
        } else {
            return 'Masukan Jumlah'
        }
    },
    params: [],
    message: 'Masukan Jumlah'
});