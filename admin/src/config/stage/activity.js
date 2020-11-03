const activityRouter = {
  route: null,
  name: null,
  title: '活动管理',
  type: 'folder', // 类型: folder, tab, view
  icon: 'iconfont icon-tushuguanli',
  filePath: 'views/activity/', // 文件路径
  order: null,
  inNav: true,
  children: [
    {
      title: '添加活动',
      type: 'view',
      name: 'activityAdd',
      route: '/activity/add',
      filePath: 'views/activity/ActivityAdd.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '活动列表',
      type: 'view',
      name: 'activityList',
      route: '/activity/list',
      filePath: 'views/activity/ActivityList.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '活动假人',
      type: 'view',
      name: 'users-fakers',
      route: '/activity/fakers',
      filePath: 'views/activity/activityFakers.vue',
      inNav: true,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '活动Winner',
      type: 'folder',
      name: 'users-winners',
      route: '/activity/fakers',
      filePath: 'views/activity/winner',
      inNav: true,
      icon: 'iconfont icon-tushuguanli',
      children: [
        {
          title: 'Winner列表',
          type: 'view',
          name: 'winners-timeline',
          route: '/activity/winner-history',
          filePath: 'views/activity/winner/winnersList.vue',
          inNav: true,
          icon: 'iconfont icon-tushuguanli'
        },
        {
          title: '挑选Winner',
          type: 'view',
          name: 'winners-selection',
          route: '/activity/select-winners',
          filePath: 'views/activity/winner/activityWinners.vue',
          inNav: true,
          icon: 'iconfont icon-tushuguanli'
        }
      ]
    }
  ]
}

export default activityRouter
