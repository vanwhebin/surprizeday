const sponsorRouter = {
  route: null,
  name: null,
  title: '赞助商管理',
  type: 'folder', // 类型: folder, tab, view
  icon: 'iconfont icon-tushuguanli',
  filePath: 'views/sponsor/', // 文件路径
  order: null,
  inNav: true,
  children: [
    {
      title: '添加赞助',
      type: 'view',
      name: 'SponsorAdd',
      route: '/sponsor/add',
      filePath: 'views/sponsor/SponsorAdd.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '赞助列表',
      type: 'view',
      name: 'SponsorList',
      route: '/sponsor/list',
      filePath: 'views/sponsor/SponsorList.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    }
  ]
}

export default sponsorRouter
