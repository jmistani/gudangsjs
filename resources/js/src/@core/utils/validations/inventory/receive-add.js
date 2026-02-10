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

export const myRule = extend('myRule', {
    validate(value, { list, prop, key }) {
        if (list == null) return "Masukkan barang"

        var result = list.filter(item => {
            return item[prop] == value
        })
        if (result.length > 1) {
            return "Barang sudah ada dalam daftar"
        } else {
            return true
        }
    },
    params: ['list', 'prop', 'key'],
    message: 'Barang sudah ada dalam daftar!!'
});
export const hargaTerimaBarang = extend('hargaTerimaBarang', {
    validate(value, { list, prop, key, index }) {
        if (list[index][prop] == null) return null
        if (parseFloat(value) > 0) {
            return true
        } else {
            return false
        }
    },
    params: ['list', 'prop', 'key', 'index'],
    message: '*'
});
export const numOfItemTerimaBarang = extend('numOfItemTerimaBarang', {
    validate(value) {
        if (parseInt(value) > 0) {
            return true
        } else {
            return false
        }
    },
    params: [],
    message: 'Masukkan barang yang diterima'
});
export const requiredSatuan = extend('requiredSatuan', {
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
export const requiredInventory = extend('requiredInventory', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan barang"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredVendor = extend('requiredVendor', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan Vendor"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredCode = extend('requiredCode', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan kode"
        } else {
            return axios.get(`/validation/inventory-receive/kode/${value}`).then((response) => {
                // Notice that we return an object containing both a valid property and a data property.
                if (response.data.payload.valid == false)
                    return "Kode sudah terpakai"
                else
                    return true
            })
        }
    },
    computesRequired: true,
    params: [],
    message: 'Masukkan kode'
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
    message: '*'
});
export const requiredStaff = extend('requiredStaff', {
    validate(value) {
        if (value == '' || value == null) {
            return "Masukkan nama staff"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});
export const requiredUnit = extend('requiredUnit', {
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
export const requiredAccount = extend('requiredAccount', {
    validate(value) {
        if (value == '' || value == null) {
            return "Pilih Jurnal Biaya"
        } else {
            return true
        }
    },
    computesRequired: true,
    params: [],
    message: '*'
});