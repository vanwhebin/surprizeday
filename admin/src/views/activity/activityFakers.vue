<template>

    <div class="container">
        <el-card class="box-card">
            <el-form :inline="true" :model="formInline" :rules="rules">
                <el-form-item label="活动名称">
                    <el-select v-model="formInline.activity_id"
                               filterable
                               clearable
                               size="medium"
                               remote
                               :remote-method="searchActivity"
                               :loading="searchLoading"
                               reserve-keyword
                               placeholder="请输入关键词查找"
                    >
                        <el-option
                                v-for="item in activityArr"
                                :key="item.id"
                                :label="item.title"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="随机数量">
                    <el-input-number v-model="rand" :min="0" :max="1000" size="medium" label="随机数量"></el-input-number>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="getRandFakers">获取</el-button>
                </el-form-item>
                <el-form-item>
                    <el-switch
                            v-model="selectAll"
                            inactive-color="#b8babf"
                            active-color="#3963bc"
                            active-value="1"
                            inactive-value="0"
                            active-text="全选"
                            @change="toggleSelectAll"
                            inactive-text="全不选">
                        >
                    </el-switch>
                </el-form-item>
                <el-form-item>
                    <el-button type="submit" class="el-button--primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
            <div class="lin-container">
                <div class="lin-wrap container">
                    <div class="imgs-upload-container">
                        <div
                                class="img-box"
                                element-loading-text="拼命加载中"
                                element-loading-spinner="el-icon-loading"
                                @click="unChoose(item,index)"
                                v-for="(item, index) in selectedFakers"
                                :key="index">
                            <el-image
                                    class="thumb-item-img"
                                    :src="item.user.avatar"
                                    fit="cover"
                                    style="width: 100%; height: 100%;">
                            </el-image>

                            <div class="control selected">
                                <div class="preview">
                                    <span style="font-size:12px;display:block">{{ item.user.name }}</span>
                                    <i class="el-icon-check"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="lin-wrap container">
                    <div class="imgs-upload-container">
                        <div
                                class="img-box"
                                element-loading-text="拼命加载中"
                                element-loading-spinner="el-icon-loading"
                                @click="choose(item,index)"
                                v-for="(item, index) in fakers"
                                :key="index">
                            <el-image
                                    class="thumb-item-img"
                                    :src="item.user.avatar"
                                    fit="cover"
                                    style="width: 100%; height: 100%;">
                            </el-image>

                            <div class="control">
                                <div class="preview">
                                    <span style="font-size:12px;display:block">{{ item.user.name }}</span>
                                    <i class="el-icon-check"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="pagination">
                    <el-pagination
                            @current-change="handleCurrentChange"
                            :background="true"
                            :page-size="pageCount"
                            :current-page="currentPage"
                            v-if="refreshPagination"
                            layout="prev, pager, next, jumper"
                            :total="total_nums"
                    ></el-pagination>
                </div>
            </div>
        </el-card>
    </div>
</template>

<script>
import Users from '@/models/users';
import Activity from '@/models/activity';

  export default {
    async created() {
      this.loading = true
      this.getFakers(this.pageCount, this.currentPage, 10)
      this.loading = false
    },
    data() {
      return {
        loading: false,
        searchLoading: false,
        refreshPagination: true, // 页数增加的时候，因为缓存的缘故，需要刷新Pagination组件
        currentPage: 1, // 默认获取第一页的数据
        pageCount: 100, // 每页100条数据
        total_nums: 0, // 分组内的用户总数
        formInline: {
          activity_id: ''
        },
        rand: 1,
        user_ids: [],
        rules: {
          activity_id: [
            {
              required: true,
              message: '请输入活动名称关键字',
              trigger: 'blur'
            }
          ]
        },
        randRules: {
          rand: [
            {
              required: true,
              message: '请输入随机数量',
              trigger: 'blur'
            }
          ]
        },
        selectAll: 0,
        activityArr: [],
        fakers: [],
        selectedFakers: []
      }
    },
    methods: {
      async getFakers(num, page, rand) {
        let count = num || this.pageCount
        let p = page || this.currentPage
        try {
          const fakersData = await Users.getFakers(count, p, rand)
          console.log(fakersData)
          this.fakers = fakersData.data
          this.total_nums = fakersData.total
        } catch (error) {
          console.log(error)
        }
      },
      async getRandFakers() {
        try {
          if (this.rand < 10) {
            this.$message.error('请至少选择随机数量在10个以上')
            return false
          }
          const fakersData = await Users.getFakers(1, 1, this.rand)
          this.total_nums = this.rand
          this.currentPage = 1
          this.pageCount = this.rand
          this.fakers = fakersData.data
          this.total_nums = fakersData.total
        } catch (error) {
          console.log(error)
        }
      },
      async searchActivity(query) {
        try {
          const activity = await Activity.searchActivity(50, 1, query)
          console.log(activity)
          this.activityArr = activity.data
        } catch (error) {
          if (error.error_code === 10020) {
            this.tableData = []
          }
        }
      },
      toggleSelectAll(val) {
        let _this = this
        if (parseInt(val) === 1) {
          this.fakers.forEach((item) => {
            _this.user_ids.push(item.user_id)
          })
          this.selectedFakers = this.fakers
          this.fakers = []
        } else {
          this.selectedFakers.forEach((item)=>{
            if (_this.fakers.indexOf(item.user_id) === -1) {
              _this.fakers.push(item)
            }
          })

          this.user_ids = []
          this.selectedFakers = []
        }
        console.log(this.user_ids)
      },
      choose(item, index) {
        let ind = this.user_ids.indexOf(item.user_id)
        if (ind === -1) {
          this.fakers.splice(index, 1)
          this.selectedFakers.push(item)
          this.user_ids.push(item.user_id)
        }
        console.log(this.fakers)
        console.log(this.user_ids)
      },
      unChoose(item, index) {
        let ind = this.user_ids.indexOf(item.user_id)
        this.fakers.push(item);
        this.selectedFakers.splice(index, 1)
        this.user_ids.splice(ind, 1)
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.getFakers(this.pageCount, this.currentPage, 0)
      },
      async save(){
        try {
            if (this.beforeSubmitHandler()) {
              let data = {user_ids: this.user_ids}
              let res = await Activity.addActivityFakers(this.formInline.activity_id, data)
              console.log(res)
              if (res.error_code === 0) {
                this.$message.success(`${res.msg}`)
                this.reset()
              } else {
                this.$message.error(`${res.msg}`)
              }
            }
        } catch (error) {
          console.log(error)
        }
      },
      reset() {
        this.rand = 1
        this.selectAll = 0
        this.user_ids = []
        this.selectedFakers = []
        this.formInline.activity_id = ''
        this.getFakers(50, 1, 0)
      },
      beforeSubmitHandler() {
        if (this.user_ids.length === 0) {
          this.$message.error(`请至少选择一个用户`)
          return false
        }

        if (!this.formInline.activity_id) {
          this.$message.error(`请选择对应活动`)
          return false
        }
        return true
      }
    }
  }
</script>

<style lang="scss" scoped>
    .container {
        padding: 20px 30px;

        .imgs-upload-container {
            display: flex;
            flex-wrap: wrap;

            .img-box {
                border: 1px dashed #d9d9d9;
                border-radius: 3px;
                -webkit-transition: all .1s;
                transition: all .1s;
                color: #666666;
                margin-right: 1em;
                margin-bottom: 1em;
                width: 75px;
                height: 75px;
                cursor: pointer;
                font-size: 12px;
                text-align: center;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                line-height: 1.3;
                flex-direction: column;

                .el-image {
                    width: 100%;
                    height: 100%;
                }

                .control {
                    display: flex;
                    -webkit-box-align: center;
                    -ms-flex-align: center;
                    align-items: center;
                    -webkit-box-pack: center;
                    -ms-flex-pack: center;
                    justify-content: center;
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    opacity: 0;
                    background-color: rgba(0, 0, 0, 0.3);
                    -webkit-transition: all .3s;
                    transition: all .3s;
                    -webkit-transition-delay: .1s;
                    transition-delay: .1s;

                    .preview {
                        color: white;
                        font-size: 2em;
                        transition: all .2s;
                    }


                }
                &:hover {
                    .control {
                        opacity: 1;
                    }
                }

                .selected {
                    opacity: 1;
                }


            }
        }
    }
</style>
