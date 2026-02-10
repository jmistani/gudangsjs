import useSanctum from '@core/auth/sanctum/useSanctum'
import axios from '@axios'

const { sanctum } = useSanctum(axios, {})
export default sanctum