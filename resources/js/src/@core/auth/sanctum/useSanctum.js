import SanctumService from './sanctumService'

export default function useSanctum(axiosIns, sanctumOverrideConfig) {
    const sanctum = new SanctumService(axiosIns, sanctumOverrideConfig)

    return {
        sanctum,
    }
}