<template>
  <div class="container">
    <div class="title">
      <span>编辑红人信息</span>
    </div>
    <el-divider></el-divider>
    <div class="wrap">
      <el-row>
        <el-card>
          <el-col
                  :lg="16"
                  :md="20"
                  :sm="24"
                  :xs="24">
            <el-form
                    :rules="editRules"
                    :model="form"
                    status-icon
                    ref="form"
                    label-width="100px"
                    v-loading="loading"
                    @submit.native.prevent>
              <el-form-item label="名称" prop="name">
                <el-input size="medium" v-model="form.name" placeholder="请填写红人名称"></el-input>
              </el-form-item>
              <el-form-item label="昵称" prop="nickname">
                <el-input size="medium" v-model="form.nickname" placeholder="请填写红人昵称"></el-input>
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
              <el-form-item label="备注" prop="memo">
                <el-input
                        size="medium"
                        type="textarea"
                        :autosize="{ minRows: 4, maxRows: 8}"
                        placeholder="烂笔头胜过好记性"
                        v-model="form.memo"
                ></el-input>
              </el-form-item>
              <el-form-item class="submit">
                <el-button type="primary" @click="submitForm('form')">保 存</el-button>
                <el-button @click="back">返 回</el-button>
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
  props: {
    editinfluID: {
      type: Number
    }
  },
  data() {
    return {
      loading: false,
      form: {
        id: 0,
        name: '',
        nickname: '',
        memo: '',
        platform: [],
        email: ''
      },
      editRules: {
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
    this.loading = true
    const platformArr = await influ.getInfluencerSource()
    this.initialPlatform(platformArr)
    const data = await influ.getInfluencer(this.editinfluID)
    this.initialInfluPlatform(data.relationship)
    this.form.name = data.name
    this.form.nickname = data.nickname
    this.form.memo = data.memo
    this.form.id = data.id
    this.form.email = data.email
    this.loading = false
  },
  methods: {
    async submitForm(formName) {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }

      try {
        const res = await influ.editInfluencer(this.editinfluID, this.form)
        if (res.error_code === 0) {
          this.$message.success(`${res.msg}`)
          this.resetForm(formName)
          this.$emit('editClose')
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
      platformArr.forEach((item) => {
        console.log(item)
        item.label = item.name
        item.value = item.id
        return item
      })
      this.platformOptions = platformArr
    },
    initialInfluPlatform(data) {
      console.log(data)
      data.forEach((item) => {
        this.form.platform.push(item.source.id)
      })
    },
    back() {
      this.$emit('editClose')
    }
  }
}
</script>

<style lang="scss" scoped>
.el-divider--horizontal {
  margin: 0
}

.container {
  .title {
    height: 59px;
    line-height: 59px;
    color: $parent-title-color;
    font-size: 16px;
    font-weight: 500;
    text-indent: 40px;

    .back {
      float: right;
      margin-right: 40px;
      cursor: pointer;
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
