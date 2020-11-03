/* eslint-disable class-methods-use-this */
import {
  post,
  get,
  put,
  _delete
} from '@/lin/plugins/axios'

// 我们通过 class 这样的语法糖使模型这个概念更加具象化，其优点：耦合性低、可维护性。
class Activity {
  // constructor() {}

  // 类中的方法可以代表一个用户行为
  async addActivity(info) {
    const res = await post('cms/activity', info)
    return res
  }

  // 在这里通过 async await 语法糖让代码同步执行
  // 1. await 一定要搭配 async 来使用
  // 2. await 后面跟的是一个 Promise 对象
  async editActivity(id, info) {
    const res = await put(`cms/activity/${id}`, info)
    return res
  }

  async deleteActivity(id) {
    const res = await _delete(`cms/activity/${id}`)
    return res
  }

  async getActivitys(num, page) {
    const res = await get('cms/activity', { num, page })
    return res
  }

  async getDataByQuery(query) {
    const res = await get('cms/activity/search', { query })
    return res
  }

  async getActivity(id) {
    const res = await get(`cms/activity/${id}`)
    return res
  }

  async hideActivity(id) {
    const res = await post(`cms/activity/${id}`)
    return res
  }

  async addActivityFakers(id,user_ids) {
    const res = await put(`cms/activity/${id}/fakers`, user_ids)
    return res
  }

  async searchActivity(num, page, query) {
    const res = await get('cms/activity/search', { num, page, query })
    return res
  }

  async addActivityWinners(id,user_ids) {
    const res = await put(`cms/activity/${id}/winners`, user_ids)
    return res
  }

  async getActivityUsers(id, num, page, name, fake) {
    const res = await get(`cms/activity/${id}/users`, { num, page, name, fake })
    return res
  }
}

export default new Activity()
