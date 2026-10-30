
export default [
  {
    header: 'Barang & Stok',
  },
  {
    title: 'Gudang',
    icon: 'TrelloIcon',
    tagVariant: 'light-warning',
    children: [
      {
        title: 'Dashboard',
        route: 'inventory-dashboard',
        resource: 'inventory',
        action: 'list',
      },
      {
        title: 'Daftar Barang',
        route: 'inventory-list',
        resource: 'inventory',
        action: 'list',
      },
      {
        title: 'Terima Barang',
        route: 'inventory-receive-add',
        resource: 'inventory',
        action: 'receive',
      },
      {
        title: 'Keluar Barang',
        route: 'inventory-consume-add',
        resource: 'inventory',
        action: 'checkout',
      },
    ],
  },
]
