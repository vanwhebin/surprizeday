const influRouter = {
  route: null,
  name: null,
  title: '红人管理',
  type: 'folder', // 类型: folder, tab, view
  icon: 'iconfont icon-tushuguanli',
  filePath: 'views/influencer/', // 文件路径
  order: null,
  inNav: true,
  children: [
    {
      title: '红人列表',
      type: 'view',
      name: 'influencerList',
      route: '/influencer/list',
      filePath: 'views/influencer/influList.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '添加红人',
      type: 'view',
      name: 'influencerAdd',
      route: '/influencer/add',
      filePath: 'views/influencer/influAdd.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '红人活动列表',
      type: 'view',
      name: 'influencerActivityList',
      route: '/influencerActivity/list',
      filePath: 'views/influencer/influActivityList.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    }
  ]
}

export default influRouter
