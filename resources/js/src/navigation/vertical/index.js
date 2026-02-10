import gudang from './gudang'
import pengaturan from './pengaturan'
import laporan from './laporan'
import admin from './admin'

export default [
    ...gudang,
    ...pengaturan,
    ...laporan,
    ...admin,
    // {
    //   title: 'Home',
    //   route: 'home',
    //   icon: 'HomeIcon',
    // },
    // {
    //   title: 'Second Page',
    //   route: 'second-page',
    //   icon: 'FileIcon',
    // },
]