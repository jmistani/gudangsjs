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
import axios from '@axios'

export const requiredNamaBank = extend('requiredNamaBank', {
    validate(value) {
        if (value == '' || value == null) {
            return "*"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredNamaRekening = extend('requiredNamaRekening', {
    validate(value) {
        if (value == '' || value == null) {
            return "*"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredNomorRekening = extend('requiredNomorRekening', {
    validate(value) {
        if (value == '' || value == null) {
            return "*"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredAlias = extend('requiredAlias', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan tanggal"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});