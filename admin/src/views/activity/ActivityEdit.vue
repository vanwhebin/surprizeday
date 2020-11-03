<template>
  <div class="container">
    <div class="title">
      <span>修改活动信息</span>
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
                        :active-value="active"
                        :inactive-value="inactive"
                        active-text="显示"
                        inactive-text="隐藏">
                  >
                  {{ form.status }}
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
                           :active-value="active"
                           :inactive-value="inactive"
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
              <el-form-item label="活动奖品" prop="prize">
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
              <el-form-item label="赞助商" prop="sponsor">
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
              <el-form-item label="定时发布" >
                <el-switch
                        v-model="timer"
                        inactive-color="#b8babf"
                        active-color="#3963bc"
                        active-value="1"
                        inactive-value="0"
                        active-text="定时"
                        @change="setTimer"
                        inactive-text="不定时">
                  >
                </el-switch>
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


    <el-dialog :visible.sync="timeDialog"  @close="closeTimerDialog" :modal="true" title="设置定时发布活动时间" width="30%">
      <el-form :rules="timerRule"
              :model="timerForm"
              status-icon
               :inline="true"
              ref="timerForm"
              label-width="100px"
              @submit.native.prevent>
        <el-form-item prop="start_time">
          <el-date-picker
                  v-model="timerForm.start_time"
                  type="datetime"
                  align="center"
                  placeholder="服务器时间(PDT)"
                  value-format="timestamp"
          >
          </el-date-picker>
        </el-form-item>
        <el-form-item >
          <el-button type="primary" @click="submitTimerForm">保 存</el-button>
          <el-button type="default outline" @click="closeTimerDialog">取 消</el-button>
        </el-form-item>
      </el-form>
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
  props: {
    editActivityID: {
      type: Number
    }
  },
  data() {
    return {
      form: {
        title: '',
        seo_title: '',
        description: '',
        sponsor_id: 0,
        prize_id: 0,
        order: 0,
        type: 2,
        private: 0,
        thumb: '',
        start_time: 0,
        status: 0,
        scheduleTime: 0
      },
      timerForm: {
        start_time: 0
      },
      timerRule: {
        start_time: [
          {
            required: true,
            message: '请输入自动发布时间',
            trigger: 'blur'
          }
        ]
      },
      timer: 0,
      timeDialog: false,
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
          }
        ]
      },
      uploadUrl: config.uploadUrl,
      dialogImageUrl: '',
      dialogVisible: false,
      prizes: [],
      sponsors: [],
      prizeImages: [],
      active: 1,
      inactive: 0,
      loading: false,
      types: [
        { id: 1, label: '普通活动' },
        { id: 2, label: '组团活动' }
      ]
    }
  },
  async created() {
    this.loading = true
    this.form = await Activity.getActivity(this.editActivityID)
    this.prize = await Prize.getActivityPrize(this.editActivityID)
    const sponsors = await Sponsor.getSponsors()
    this.form.start_time = this.dateFormatter(this.form.start_time)
    this.form.prize_id = this.prize.id
    this.initThumb(this.form.thumb)
    this.initDesc(this.form.description)
    this.sponsors = sponsors.data
    this.prizes.push(this.prize)
    this.loading = false
  },
  methods: {
    async submitForm() {
      try {
        this.$refs.form.validate(async (valid) => {
          if (valid) {
            const res = await Activity.editActivity(this.editActivityID, this.form)
            if (res.error_code === 0) {
              this.$message.success(`${res.msg}`)
              this.$emit('editClose')
            } else {
              this.$message.error(`${res.msg}`)
            }
          }
        })
      } catch (error) {
        this.$message.error(`${res.msg}`)
      }
    },
    descChange(value) {
      this.form.description = value
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
    initThumb(thumb) {
      this.form.thumb = thumb.id
      const tmpArr = []
      tmpArr.name = thumb.id ? thumb.id : thumb.url
      tmpArr.url = thumb.url
      this.upload.uploadedFileList.push(tmpArr)
    },
    initDesc(desc) {
      this.$refs.descEditor.content = desc
    },
    async getSponsors() {
      try {
        const sponsors = await Sponsor.getSponsors(9999, 1)
        this.sponsors = sponsors.data
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    async searchPrize(query) {
      try {
        const prizes = await Prize.searchPrize(50, 1, query)
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
    setTimer(val) {
      if (parseInt(val) === 1) {
        this.timeDialog = true
      }
    },
    submitTimerForm (){
      this.$refs.timerForm.validate((valid) => {
        if (valid) {
          console.log(this.timerForm.start_time)
          if (this.timerForm.start_time < (new Date().getTime())) {
            this.$message.error("定时发布时间必须大于当前时间");
            return false
          }
          this.form.scheduleTime = (this.timerForm.start_time / 1000)
          this.timeDialog = false
          this.timer = 1
        }
      })
    },
    closeTimerDialog(){
      this.timeDialog = false
      this.timer = 0
    },
    dateFormatter(timestamp) {
      if (timestamp) {
        return (timestamp * 1000)
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
