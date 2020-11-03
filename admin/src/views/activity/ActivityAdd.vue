<template>
  <div class="container">
    <div class="header">
      <div class="title">新建活动</div>
      <router-link tag="a" to="/activity/list">
        <el-button type="primary" plain >返回</el-button>
      </router-link>
    </div>
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
                          :rules="rules"
                          label-width="100px"
                          v-loading="loading"
                          @submit.native.prevent>
                      <el-form-item label="活动名称" prop="title">
                          <el-input size="medium" v-model="form.title" placeholder="请填写活动名称"></el-input>
                      </el-form-item>
                      <el-form-item label="分享标题" prop="seo_title">
                          <el-input size="medium" v-model="form.seo_title" placeholder="请填写活动社交媒体分享标题"></el-input>
                      </el-form-item>
                      <el-form-item label="活动封面" prop="thumb">
                          <el-upload
                                  ref="uploadCover"
                                  :action="uploadUrl"
                                  list-type="picture-card"
                                  :file-list="upload.uploadedFileList"
                                  :accept="upload.accept"
                                  :on-preview="handlePictureCardPreview"
                                  :limit="upload.limit"
                                  :on-remove="handleRemove"
                                  :on-exceed="exceedTip"
                                  :on-success="handleUploaded"
                          >
                              <i class="el-icon-plus"></i>
                          </el-upload>
                          <el-dialog :visible.sync="dialogVisible">
                              <img width="100%" :src="dialogImageUrl" alt="">
                          </el-dialog>
                      </el-form-item>
                      <el-form-item label="活动状态" prop="status">
                          <el-switch
                                  v-model="form.status"
                                  active-color="#13ce66"
                                  inactive-color="#ff4949"
                                  active-value="1"
                                  inactive-value="0"
                                  active-text="显示"
                                  inactive-text="隐藏">
                              >
                          </el-switch>
                      </el-form-item>
                      <el-form-item  label="活动类型" prop="type">
                          <el-select  size="medium"  v-model="form.type" placeholder="请选择">
                              <el-option
                                      v-for="item in types"
                                      :key="item.id"
                                      :label="item.label"
                                      :value="item.id">
                              </el-option>
                          </el-select>
                      </el-form-item>
                      <el-form-item label="私密活动" prop="private">
                          <el-switch v-model="form.private"
                                     inactive-color="#3963bc"
                                     active-color="#848788cc"
                                     active-value="1"
                                     inactive-value="0"
                                     active-text="私密"
                                     inactive-text="公开">
                              >
                          </el-switch>
                      </el-form-item>
                      <el-form-item label="开奖时间" prop="start_time">
                          <el-date-picker
                                  v-model="form.start_time"
                                  type="datetime"
                                  align="center"
                                  placeholder="服务器时间(PDT)"
                                  value-format="timestamp"
                          >
                          </el-date-picker>
                      </el-form-item>
                      <el-form-item label="活动奖品" prop="prize_id">
                          <el-select v-model="form.prize_id"
                                     filterable
                                     clearable
                                     size="medium"
                                     remote
                                     :remote-method="searchPrize"
                                     :loading="searchLoading"
                                     reserve-keyword
                                     placeholder="请输入关键词查找"
                          >
                              <el-option
                                      v-for="item in prizes"
                                      :key="item.id"
                                      :label="item.name"
                                      :value="item.id">
                              </el-option>
                          </el-select>
                          <div class="block" v-for="(key, img) in prizeImages" :key="key">
                              <span class="demonstration">{{ img }}</span>
                              <el-image
                                      style="width: 100px; height: 100px"
                                      :src="img"
                                      :fit="fit">
                              </el-image>
                          </div>
                      </el-form-item>
                      <el-form-item label="赞助商" prop="sponsor_id">
                          <el-select v-model="form.sponsor_id" filterable clearable placeholder="请选择" size="medium">
                              <el-option
                                      v-for="it in sponsors"
                                      :key="it.id"
                                      :label="it.name"
                                      :value="it.id">
                              </el-option>
                          </el-select>
                      </el-form-item>
                      <el-form-item label="活动介绍" prop="description">
                          <tinymce  ref="descEditor" :upload_url="uploadUrl" @change=descChange  v-model="form.description" :showMenubar="false"/>
                      </el-form-item>
                      <el-form-item label="排序" prop="order">
                          <el-input-number v-model="form.order" @change="orderChange" :min="0" :max="1000" label="活动排序" size="small"></el-input-number>
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
    <el-dialog
            title="提示"
            :visible.sync="resDialogVisible"
            width="30%"
            :before-close="handleClose">
      <span>{{ res.msg }}</span>
      <span slot="footer" class="dialog-footer">
    <el-button @click="resetForm('form')">新建活动</el-button>
    <router-link tag="a" to="/activity/list">
      <el-button type="primary" plain >查看列表</el-button>
    </router-link>
  </span>
    </el-dialog>

  </div>
</template>

<script>
import moment from 'moment'
import Tinymce from '@/components/base/tinymce'
import Activity from '@/models/activity'
import Sponsor from '@/models/sponsor'
import Prize from '@/models/prize'
import config from '@/config/index'

export default {
  components: {
    Tinymce
  },
  data() {
    return {
      form: {
        title: '',
        seo_title: '',
        description: '',
        sponsor_id: '',
        thumb: '',
        prize_id: '',
        order: 0,
        type: 2,
        private: 0,
        start_time: 0,
        status: 0
      },
      prize: {
        name: '',
        id: ''
      },
      sponsor: {
        name: '',
        id: ''
      },
      searchLoading: false,
      upload: {
        multiple: false,
        limit: 1,
        accept: 'png, jpg, gif',
        uploadedFileList: []
      },
      rules: {
        description: [
          {
            required: true,
            message: '请输入活动简介',
            trigger: 'blur'
          },
          {
            max: 1500,
            message: '简介过长',
            trigger: 'blur'
          }
        ],
        prize_id: [
          {
            required: true,
            message: '请选择奖品',
            trigger: 'blur'
          }
        ],
        sponsor_id: [
          {
            required: true,
            message: '请选择赞助商',
            trigger: 'blur'
          }
        ],
        seo_title: [
          {
            required: true,
            message: '请设置分享标题',
            trigger: 'blur'
          }
        ],
        title: [
          {
            required: true,
            message: '请输入奖品标题',
            trigger: 'blur'
          },
          {
            max: 100,
            message: '奖品标题过长',
            trigger: 'blur'
          }
        ],
        thumb: [
          {
            required: true,
            message: '请上传活动封面图',
            trigger: 'blur'
          }
        ],
        start_time: [
          {
            required: true,
            message: '请选择开奖时间',
            trigger: 'blur'
          },
          {
            type: 'number',
            min: ((new Date()).getTime() + (3600 * 1000)) / 1000,
            message: '开奖时间至少要超过当前时间一小时以上',
            trigger: 'blur'
          }
        ]
      },
      uploadUrl: config.uploadUrl,
      dialogImageUrl: '',
      dialogVisible: false,
      resDialogVisible: false,
      prizes: [],
      sponsors: [],
      loading: false,
      prizeImages: [],
      types: [
        { id: 1, label: '普通活动' },
        { id: 2, label: '组团活动' }
      ],
      res: {
        code: 0,
        msg: ''
      }
    }
  },
  async created() {
    this.getSponsors()
  },
  methods: {
    submitForm(formName) {
      try {
        // let formData = this.form
        const formData = this.beforeSubmitHandler(this.form)
        console.log(formData)
        this.$refs.form.validate((valid) => {
          if (valid) {
            this.createActivity(formData, formName)
          }
        })
      } catch (error) {
        console.log(error)
      }
    },
    async createActivity(formData, formName) {
      const res = await Activity.addActivity(formData)
      this.res = res
      console.log(res)
      this.resDialogVisible = true
      // return false
      if (res.error_code === 0) {
        this.$message.success(`${res.msg}`)
        this.resetForm(formName)
      }
    },
    descChange(value) {
      this.form.description = value
      console.log(this.form)
    },
    // 重置表单
    resetForm(formName) {
      this.$refs[formName].resetFields()
      this.$refs.uploadCover.clearFiles()
      this.$refs.descEditor.content = ''
      this.form.prize_id = ''
      this.form.sponsor_id = ''
      this.form.status = 0
    },
    handleRemove(file, fileList) {
      console.log(file, fileList)
    },
    handlePictureCardPreview(file) {
      this.dialogImageUrl = file.url
      this.dialogVisible = true
    },
    exceedTip() {
      this.$message.error(`最多只能上传${this.upload.limit}张图`)
    },
    handleUploaded(response, file, fileList) {
      this.form.thumb = response[0].id
    },
    handleClose() {
      this.resDialogVisible = false
    },
    async getSponsors() {
      try {
        const sponsors = await Sponsor.getSponsors(200, 1)
        this.sponsors = sponsors.data
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    async searchPrize(query) {
      try {
        const prizes = await Prize.searchPrize(query, 99, 1)
        console.log(prizes)
        this.prizes = prizes.data
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    orderChange(num) {
      this.form.order = num
    },
    dateFormatter(timestamp) {
      if (timestamp) {
        return moment(timestamp * 1000).format('YYYY-MM-DD HH:mm:ss')
      }
    },
    beforeSubmitHandler(data) {
      // 对提交数据再进行处理
      let timestamp = data.start_time
      if (timestamp.toString().length > 11) {
        timestamp = Math.ceil(timestamp / 1000)
        data.start_time = timestamp
      }
      return data
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
