import adminConfig from './admin'
import usersConfig from './users' // 引入用户管理路由文件
import activityConfig from './activity' // 引入活动管理路由文件
import prizeConfig from './prize' // 引入奖品管理路由文件
import sponsorConfig from './sponsor' // 引入赞助商管理路由文件
import influencerConfig from './influencer' // 引入赞助商管理路由文件
import pluginsConfig from './plugins'
import Utils from '@/lin/utils/util'

// eslint-disable-next-line import/no-mutable-exports
let homeRouter = [
  {
    title: '首页',
    type: 'view',
    name: Symbol('home'),
    route: '/home',
    filePath: 'views/data/Data.vue',
    inNav: true,
    icon: 'iconfont icon-iconset0103',
    order: 0
  },
  {
    title: '404',
    type: 'view',
    name: Symbol('404'),
    route: '/404',
    filePath: 'views/error-page/404.vue',
    inNav: false,
    icon: 'iconfont icon-rizhiguanli'
  },
  activityConfig,
  prizeConfig,
  sponsorConfig,
  influencerConfig,
  usersConfig,
  adminConfig,
  {
    title: '日志管理',
    type: 'view',
    name: Symbol('log'),
    route: '/log',
    filePath: 'views/log/Log.vue',
    inNav: true,
    icon: 'iconfont icon-rizhiguanli',
    order: 10,
    right: ['查询所有日志']
  }
]

const plugins = [...pluginsConfig]

// 筛除已经被添加的插件
function filterPlugin(data) {
  if (plugins.length === 0) {
    return
  }
  if (Array.isArray(data)) {
    data.forEach((item) => {
      filterPlugin(item)
    })
  } else {
    const findResult = plugins.findIndex(item => (data === item))
    if (findResult >= 0) {
      plugins.splice(findResult, 1)
    }
    if (data.children) {
      filterPlugin(data.children)
    }
  }
}

filterPlugin(homeRouter)

homeRouter = homeRouter.concat(plugins)

// 处理顺序
homeRouter = Utils.sortByOrder(homeRouter)

// 使用 Symbol 处理 name 字段, 保证唯一性
const deepReduceName = (target) => {
  if (Array.isArray(target)) {
    target.forEach((item) => {
      if (typeof item !== 'object') {
        return
      }
      deepReduceName(item)
    })
    return
  }
  if (typeof target === 'object') {
    if (typeof target.name !== 'symbol') {
      // eslint-disable-next-line no-param-reassign
      target.name = target.name || Utils.getRandomStr()
      // eslint-disable-next-line no-param-reassign
      target.name = Symbol(target.name)
    }

    if (Array.isArray(target.children)) {
      target.children.forEach((item) => {
        if (typeof item !== 'object') {
          return
        }
        deepReduceName(item)
      })
    }
  }
}

deepReduceName(homeRouter)

export default homeRouter
