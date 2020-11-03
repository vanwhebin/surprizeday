<template>
  <div class="container">
    <div class="header">
      <div class="title">新增赞助商</div>
      <router-link tag="a" to="/sponsor/list">
        <el-button type="primary" plain >返回</el-button>
      </router-link>
    </div>
    <div class="wrap">
      <el-row>
        <el-card>
          <el-col :lg="16" :md="20" :sm="24" :xs="24">
            <el-form
                    :model="form"
                    status-icon
                    ref="form"
                    :rules="rules"
                    label-width="100px"
                    @submit.native.prevent
            >
              <el-form-item label="名称" prop="name">
                <el-input size="medium" v-model="form.name" placeholder="请填写赞助商名称"></el-input>
              </el-form-item>
              <el-form-item label="链接" prop="link">
                <el-input
                        size="medium"
                        type="text"
                        :autosize="{ minRows: 4, maxRows: 8}"
                        placeholder="请输入简介"
                        v-model="form.link"
                ></el-input>
              </el-form-item>

              <el-form-item class="submit">
                <el-button type="primary" @click="submitForm('form')">保 存</el-button>
                <el-button @click="resetForm('form')">重 置</el-button>
              </el-form-item>
            </el-form>
          </el-col>
        </el-card>
      </el-row>
    </div>
  </div>
</template>

<script>
import Sponsor from '@/models/sponsor'

export default {
  data() {
    return {
      form: {
        name: '',
        link: ''
      },
      rules: {
        link: [
          {
            max: 255,
            message: '赞助商链接过长',
            trigger: 'blur'
          }
        ],
        name: [
          {
            required: true,
            message: '请输入赞助商名称',
            trigger: 'blur'
          },
          {
            max: 255,
            message: '名称过长',
            trigger: 'blur'
          }
        ]
      }
    }
  },
  methods: {
    async submitForm(formName) {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }
      try {
        const res = await Sponsor.addSponsor(this.form)
        if (res.error_code === 0) {
          this.$message.success(`${res.msg}`)
          // this.resetForm(formName)
          this.$router.push({ path: '/sponsor/list' })
        }
      } catch (error) {
        console.log(error)
      }
    },
    // 重置表单
    resetForm(formName) {
      this.$refs[formName].resetFields()
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
