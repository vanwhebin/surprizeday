<template>

    <div class="container">
      <div class="title">
        <span>编辑赞助商信息</span>
        <span class="back" @click="back">
        <i class="iconfont icon-fanhui"></i> 返回
      </span>
      </div>
      <el-divider></el-divider>
      <div class="wrap">
        <el-card>
        <el-row>
          <el-col
                  :lg="16"
                  :md="20"
                  :sm="24"
                  :xs="24">
            <el-form
                    :model="form"
                    status-icon
                    ref="form"
                    label-width="100px"
                    v-loading="loading"
                    @submit.native.prevent>
              <el-form-item label="名称" prop="name">
                <el-input size="medium" v-model="form.name" placeholder="请填写赞助商名称"></el-input>
              </el-form-item>
              <el-form-item label="简介" prop="link">
                <el-input
                        size="medium"
                        type="textarea"
                        :autosize="{ minRows: 4, maxRows: 8}"
                        placeholder="请输入链接"
                        v-model="form.link"
                ></el-input>
              </el-form-item>
              <el-form-item class="submit">
                <el-button type="primary" @click="submitForm('form')">保 存</el-button>
                <el-button @click="resetForm('form')">重 置</el-button>
              </el-form-item>
            </el-form>
          </el-col>
        </el-row>
        </el-card>
      </div>

    </div>

</template>

<script>
import Sponsor from '@/models/sponsor'

export default {
  props: {
    editSponsorID: {
      type: Number
    }
  },
  data() {
    return {
      loading: false,
      form: {
        id: 0,
        name: '',
        link: ''
      },
      rules: {
        link: [
          {
            max: 255,
            message: '简介过长',
            trigger: 'blur'
          }
        ],
        name: [
          {
            required: true,
            message: '请输入奖品标题',
            trigger: 'blur'
          },
          {
            max: 80,
            message: '奖品标题过长',
            trigger: 'blur'
          }
        ]
      }
    }
  },
  async mounted() {
    this.loading = true
    const data = await Sponsor.getSponsor(this.editSponsorID)
    this.form.name = data.name
    this.form.link = data.link
    this.form.id = data.id
    this.loading = false
  },
  methods: {
    async submitForm() {
      const res = await Sponsor.editSponsor(this.editSponsorID, this.form)
      if (res.error_code === 0) {
        this.$message.success(`${res.msg}`)
        this.$emit('editClose')
      }
    },
    // 重置表单
    resetForm(formName) {
      this.$refs[formName].resetFields()
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
