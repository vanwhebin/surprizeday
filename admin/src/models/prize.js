/* eslint-disable class-methods-use-this */
import {
  post,
  get,
  put,
  _delete
} from '@/lin/plugins/axios'

// 我们通过 class 这样的语法糖使模型这个概念更加具象化，其优点：耦合性低、可维护性。
class Prize {
  // constructor() {}

  // 类中的方法可以代表一个用户行为
  async addPrize(info) {
    const res = await post('cms/prize', info)
    return res
  }

  // 在这里通过 async await 语法糖让代码同步执行
  // 1. await 一定要搭配 async 来使用
  // 2. await 后面跟的是一个 Promise 对象
  async getPrize(id) {
    const res = await get(`cms/prize/${id}`)
    return res
  }

  async editPrize(id, info) {
    const res = await put(`cms/prize/${id}`, info)
    return res
  }

  async deletePrize(id) {
    const res = await _delete(`cms/prize/${id}`)
    return res
  }

  async getPrizes(num, page) {
    const res = await get('cms/prize', { num, page })
    return res
  }

  async searchPrize(query, num, page) {
    const res = await get('cms/prize/search', { query, num, page })
    return res
  }

  async getActivityPrize(activity_id) {
    const res = await get('cms/prize/activity', { activity_id })
    return res
  }
}

export default new Prize()
