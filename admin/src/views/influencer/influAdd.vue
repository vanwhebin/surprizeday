<template>
  <div class="container">
    <div class="header">
      <div class="title">添加红人</div>
    </div>
    <div class="wrap">
      <el-row>
          <el-card>
              <el-col :lg="16" :md="20" :sm="24" :xs="24">
                  <el-form
                          :model="form"
                          status-icon
                          ref="form"
                          :rules="addRules"
                          label-width="100px"
                          @submit.native.prevent
                  >
                      <el-form-item label="名称" prop="name">
                          <el-input size="medium" v-model="form.name" placeholder="请填写红人名称"></el-input>
                      </el-form-item>
                      <el-form-item label="昵称" prop="nickname">
                          <el-input size="medium" v-model="form.nickname" placeholder="请填写红人网络昵称"></el-input>
                      </el-form-item>
                      <el-form-item label="红人平台" prop="platform">
                          <el-select v-model="form.platform" multiple placeholder="请选择">
                              <el-option
                                      v-for="item in platformOptions"
                                      :key="item.value"
                                      :label="item.label"
                                      :value="item.value">
                              </el-option>
                          </el-select>
                      </el-form-item>
                      <el-form-item label="邮箱" prop="email">
                          <el-input size="medium" v-model="form.email" placeholder="请填写红人联系邮箱"></el-input>
                      </el-form-item>
                      <el-form-item label="简介" prop="memo">
                          <el-input
                                  size="medium"
                                  type="textarea"
                                  :autosize="{ minRows: 4, maxRows: 8}"
                                  placeholder="请输入简介"
                                  v-model="form.memo"
                          ></el-input>
                      </el-form-item>

                      <el-form-item class="submit">
                          <el-button type="primary" @click="submitForm('form')">保 存</el-button>
                          <router-link tag="a" to="/influencer/list"><el-button >返 回</el-button></router-link>
                      </el-form-item>
                  </el-form>
              </el-col>
          </el-card>
      </el-row>
    </div>
  </div>
</template>

<script>
import influ from '@/models/influencer'

export default {
  data() {
    return {
      form: {
        name: '',
        nickname: '',
        platform: [],
        memo: '',
        email: ''
      },
      addRules: {
        name: [
          {
            required: true,
            message: '请输入名称',
            trigger: 'blur'
          },
          {
            max: 80,
            message: '名称过长',
            trigger: 'blur'
          }
        ],
        nickname: [
          {
            required: true,
            message: '请输入昵称',
            trigger: 'blur'
          },
          {
            max: 80,
            message: '名称过长',
            trigger: 'blur'
          }
        ],
        email: [
          {
            required: true,
            message: '请输入联系邮箱',
            trigger: 'blur'
          }
        ],
        platform: [
          {
            required: true,
            message: '请至少选择一个平台名称',
            trigger: 'blur'
          }
        ]
      },
      platformOptions: []
    }
  },
  async mounted() {
    const platformArr = await influ.getInfluencerSource()
    this.initialPlatform(platformArr)
  },
  methods: {
    async submitForm(formName) {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }
      try {
        const res = await influ.addInfluencer(this.form)
        if (res.error_code === 0) {
          this.$message.success(`${res.msg}`)
          this.resetForm(formName)
        }
      } catch (error) {
        console.log(error)
      }
    },
    // 重置表单
    resetForm(formName) {
      this.$refs[formName].resetFields()
    },
    initialPlatform(platformArr) {
    //   console.log(platformArr)
      platformArr.forEach((item) => {
        item.label = item.name
        item.value = item.id
        return item
      })
      this.platformOptions = platformArr
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  padding: 0 30px;
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .title {
      height: 59px;
      line-height: 59px;
      color: $parent-title-color;
      font-size: 16px;
      font-weight: 500;
    }
  }

  .wrap {
    padding: 20px;
  }

  .submit {
    float: left;
  }
}
</style>
