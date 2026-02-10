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
import en from 'vee-validate/dist/locale/en.json'
import axios from '@axios'

export const usernameValidation = extend('usernameValidation', {
    validate(value) {
        return axios.get(`/validation/username/${value}`).then((response) => {
            if (response.data.payload.valid == false)
                return response.data.payload.message
            else
                return true
        })
    },
    computesRequired: true,
    params: [],
    message: 'Masukkan username'
});