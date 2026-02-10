import { $themeBreakpoints } from '@themeConfig'

export default {
    namespaced: true,
    state: {
        users: [],
        page: 0,
        total: 0,
        per_page: 0,
    },
    getters: {
        getUsers() {

        },
        mutations: {
            UPDATE_WINDOW_WIDTH(state, val) {
                state.windowWidth = val
            },
            TOGGLE_OVERLAY(state, val) {
                state.shallShowOverlay = val !== undefined ? val : !state.shallShowOverlay
            },
        },
        actions: {},
    }