<template>
  <div class="container">
    <div class="header">
      <div class="title">新建奖品</div>
      <router-link tag="a" to="/prize/list">
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
                          :rules="addRules"
                          label-width="100px"
                          @submit.native.prevent
                  >
                      <el-form-item label="名称" prop="name">
                          <el-input size="medium" v-model="form.name" placeholder="请填写奖品名称"></el-input>
                      </el-form-item>
                      <el-form-item label="图片" prop="img_id" v-model="form.img_id">
                          <el-upload
                                  ref="uploadPrize"
                                  :action="uploadUrl"
                                  :multiple="upload.multiple"
                                  :accept="upload.accept"
                                  @change="changeOnUpload"
                                  list-type="picture-card"
                                  :on-preview="handlePictureCardPreview"
                                  :limit="upload.limit"
                                  :on-exceed="exceedTip"
                                  :on-success="handleUploaded"
                                  :on-remove="handleRemove"
                          >
                              <i class="el-icon-plus"></i>
                          </el-upload>
                          <el-dialog :visible.sync="dialogVisible">
                              <img width="100%" :src="dialogImageUrl" alt>
                          </el-dialog>
                      </el-form-item>
                      <!--<el-form-item label="视频" prop="video_id" v-model="form.video_id">
                            <el-upload
                                    :action="uploadUrl"
                                    list-type="picture-card"
                                    :on-preview="handlePictureCardPreview"
                                    :on-remove="handleRemove">
                              <i class="el-icon-plus"></i>
                            </el-upload>
                            <el-dialog :visible.sync="dialogVisible">
                              <video width="100%" height="240" controls alt=""><source :src="dialogImageUrl"></video>
                            </el-dialog>
                      </el-form-item>-->
                      <el-form-item label="简介" prop="summary">
                          <el-input
                                  size="medium"
                                  type="textarea"
                                  :autosize="{ minRows: 4, maxRows: 8}"
                                  placeholder="请输入简介"
                                  v-model="form.summary"
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
import prize from '@/models/prize'
import config from '@/config/index'

export default {
  data() {
    return {
      form: {
        name: '',
        summary: '',
        img_id: [],
        video_id: ''
      },
      rules: {
        minWidth: 100,
        minHeight: 100,
        maxSize: 5
      },
      upload: {
        multiple: true,
        drag: true,
        limit: 8,
        accept: 'png, jpg'
      },
      addRules: {
        summary: [
          {
            required: true,
            message: '请输入奖品描述',
            trigger: 'blur'
          },
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
        ],
        img_id: [
          {
            required: true,
            message: '请上传产品图片',
            trigger: 'blur'
          }
          // {
          //   max: 5,
          //   message: "最多只能上传5张图片",
          //   trigger: "blur"
          // }
        ]
      },
      uploadUrl: config.uploadUrl,
      dialogImageUrl: '',
      dialogVisible: false
    }
  },
  methods: {
    async submitForm(formName) {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }
      try {
        const res = await prize.addPrize(this.form)
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
      this.$refs.uploadPrize.clearFiles()
    },
    changeOnUpload() {
      this.loading = true
    },
    handleRemove(file, fileList) {
      console.log(file, fileList)
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    },
    handleUploaded(response, file, fileList) {
      let uploadedPicture = this.form.img_id
      const mediaID = response[0].id
      uploadedPicture.push(mediaID)
      if (uploadedPicture.length > 8) {
        uploadedPicture = uploadedPicture.splice(-8)
      }
      // console.log(response)
      // console.log(file)
      // console.log(fileList)
      this.form.img_id = uploadedPicture
      console.log(this.form.img_id)
      this.loading = false
      return true
    },
    exceedTip(files, fileList) {
      console.log(files)
      console.log(fileList)
      this.$message.error('最多只能上传8张图')
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
