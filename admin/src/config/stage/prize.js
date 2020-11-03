const prizeRouter = {
  route: null,
  name: null,
  title: '奖品管理',
  type: 'folder', // 类型: folder, tab, view
  icon: 'iconfont icon-tushuguanli',
  filePath: 'views/prize/', // 文件路径
  order: null,
  inNav: true,
  children: [
    {
      title: '添加奖品',
      type: 'view',
      name: 'prizeAdd',
      route: '/prize/add',
      filePath: 'views/prize/PrizeAdd.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '奖品列表',
      type: 'view',
      name: 'prizeList',
      route: '/prize/list',
      filePath: 'views/prize/PrizeList.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    }
  ]
}

export default prizeRouter
