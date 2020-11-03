const userRouter = {
  route: null,
  name: null,
  title: '用户管理',
  type: 'folder', // 类型: folder, tab, view
  icon: 'iconfont icon-tushuguanli',
  filePath: 'views/users/', // 文件路径
  order: null,
  inNav: true,
  children: [
    {
      title: '编辑用户',
      type: 'view',
      name: 'bookEdit',
      route: '/users/edit',
      filePath: 'views/users/UserEdit.vue',
      inNav: false,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '添加用户',
      type: 'view',
      name: 'bookAdd',
      route: '/users/add',
      filePath: 'views/users/UserAdd.vue',
      inNav: false,
      icon: 'iconfont icon-tushuguanli'
    },
    {
      title: '用户列表',
      type: 'view',
      name: 'users-list',
      route: '/users/list',
      filePath: 'views/users/UserList.vue',
      inNav: true,
      icon: 'iconfont icon-smile'
    },
    {
      title: '用户活动日志',
      type: 'view',
      name: 'users-logs',
      route: '/users/log',
      filePath: 'views/users/UserAdd.vue',
      inNav: false,
      icon: 'iconfont icon-tushuguanli'
    }
  ]
}

export default userRouter
