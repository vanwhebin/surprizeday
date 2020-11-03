/* eslint-disable class-methods-use-this */
import {
  post,
  get,
  put,
  _delete,
} from '@/lin/plugins/axios'

// 我们通过 class 这样的语法糖使模型这个概念更加具象化，其优点：耦合性低、可维护性。
class Users {
  // constructor() {}

  // 类中的方法可以代表一个用户行为
  async addUsers(info) {
    const res = await post('cms/user', info)
    return res
  }

  // 在这里通过 async await 语法糖让代码同步执行
  // 1. await 一定要搭配 async 来使用
  // 2. await 后面跟的是一个 Promise 对象
  async getUser(id) {
    const res = await get(`cms/user/${id}`)
    return res
  }

  async editUser(id, info) {
    const res = await put(`cms/user/${id}`, info)
    return res
  }

  async delectUser(id) {
    const res = await _delete(`cms/user/${id}`)
    return res
  }

  async getUsers() {
    const res = await get('cms/users')
    return res
  }

  async getFakers(num, page, rand) {
    const res = await get('cms/user/fakers',{ num, page, rand })
    return res
  }

  async getWinners(num, page) {
    const res = await get('cms/user/winners',{ num, page })
    return res
  }

  async deleteWinners(id) {
    const res = await _delete(`cms/user/winners`, { id })
    return res
  }

}

export default new Users()
