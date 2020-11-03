/* eslint-disable class-methods-use-this */
import {
  post,
  get,
  put,
  _delete
} from '@/lin/plugins/axios'

// 我们通过 class 这样的语法糖使模型这个概念更加具象化，其优点：耦合性低、可维护性。
class Influencer {
  // constructor() {}

  // 类中的方法可以代表一个用户行为
  async addInfluencer(info) {
    const res = await post('cms/influencer', info)
    return res
  }

  // 在这里通过 async await 语法糖让代码同步执行
  // 1. await 一定要搭配 async 来使用
  // 2. await 后面跟的是一个 Promise 对象
  async getInfluencer(id) {
    const res = await get(`cms/influencer/${id}`)
    return res
  }

  async getInfluencerSource() {
    const res = await get('cms/influencer/source')
    return res
  }

  async editInfluencer(id, info) {
    const res = await put(`cms/influencer/${id}`, info)
    return res
  }

  async deleteInfluencer(id) {
    const res = await _delete(`cms/influencer/${id}`)
    return res
  }

  async getInfluencers(num, page) {
    const res = await get('cms/influencer', { num, page })
    return res
  }

  async searchInfluencer(query) {
    const res = await get('cms/influencer/search', { query })
    return res
  }

  async searchPrivateActivity(query) {
    const res = await get('cms/influencer/activity', { query })
    return res
  }

  async bindInfluActivity(data) {
    const res = await post('cms/influencer/bind', data)
    return res
  }

  async getbindInfluActivity(num, page) {
    const res = await get('cms/influencer/bind', { num, page })
    return res
  }
}

export default new Influencer()
