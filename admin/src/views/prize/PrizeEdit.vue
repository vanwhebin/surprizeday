<template>
  <div class="container">
    <div class="title">
      <span>编辑奖品信息</span>
      <span class="back" @click="back">
        <i class="iconfont icon-fanhui"></i> 返回
      </span>
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
                    :model="form"
                    status-icon
                    ref="form"
                    label-width="100px"
                    :rules="addRules"
                    v-loading="loading"
                    @submit.native.prevent>
              <el-form-item label="名称" prop="name">
                <el-input size="medium" v-model="form.name" placeholder="请填写奖品名称"></el-input>
              </el-form-item>
              <el-form-item label="图片" prop="img_id" v-model="form.img_id">
                <el-upload
                        ref="uploadPrize"
                        :action="uploadUrl"
                        :file-list="upload.uploadedFileList"
                        :multiple="upload.multiple"
                        :accept="upload.accept"
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
  props: {
    editPrizeID: {
      type: Number
    }
  },
  data() {
    return {
      loading: false,
      form: {
        id: 0,
        name: '',
        summary: '',
        img_id: [],
        video_id: ''
      },
      upload: {
        multiple: true,
        drag: true,
        limit: 8,
        accept: 'png, jpg',
        uploadedFileList: []
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
        ]
      },
      uploadUrl: config.uploadUrl,
      dialogImageUrl: '',
      dialogVisible: false
    }
  },
  async mounted() {
    this.loading = true
    const data = await prize.getPrize(this.editPrizeID)
    console.log(data)
    this.form.name = data.name
    this.form.summary = data.summary
    this.form.id = data.id
    this.initialPic(data.album)
    this.loading = false
  },
  methods: {
    async submitForm() {
      const res = await prize.editPrize(this.editPrizeID, this.form)
      if (res.error_code === 0) {
        this.$message.success(`${res.msg}`)
        this.$emit('editClose')
      }
    },
    // 重置表单
    resetForm(formName) {
      this.$refs[formName].resetFields()
      this.$refs.uploadPrize.clearFiles()
    },
    handleRemove(file, fileList) {
      console.log(file, fileList)
      console.log(this.form.img_id)
      this.form.img_id = []
      for (let i = 0; i < fileList.length; i++) {
        this.form.img_id.push(fileList[i].name)
      }
      console.log(this.form.img_id)
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
      this.form.img_id = uploadedPicture
      console.log(this.form.img_id)
      return true
    },
    exceedTip(files, fileList) {
      console.log(files)
      console.log(fileList)
      this.$message.error('最多只能上传8张图')
    },
    initialPic(album) {
      this.form.img_id = []
      for (let i = 0; i < album.length; i++) {
        const tmpArr = []
        tmpArr.name = album[i].img_id
        tmpArr.url = album[i].img.url
        this.upload.uploadedFileList.push(tmpArr)
        this.form.img_id.push(album[i].img_id)
      }
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
