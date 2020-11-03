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
                               @change="changeActivity"
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
                <el-form-item label="参与者名称">
                    <el-input v-model="formInline.name"   label="用户名称"></el-input>
                </el-form-item>
                <el-form-item label="用户分类">
                    <el-select v-model="formInline.fake" size="medium">
                        <el-option v-for="(item, index) in fakeArr" :key="index" :label="item.label"  :value="item.value"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item >
                    <el-button type="primary" @click="searchActivityUser">查询</el-button>
                </el-form-item>

            </el-form>
            <el-form :inline="true">
                <el-form-item label="奖品分级">
                    <el-select v-model="level" size="medium">
                        <el-option v-for="(item) in levelArr" :key="item.value" :label="item.label"  :value="item.value"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-button type="submit" class="el-button--primary" @click="save">保存</el-button>
                </el-form-item>
            </el-form>
            <div class="lin-container">
                <div class="clearfix"></div>
                <div class="lin-wrap container">
                    <div class="imgs-upload-container">
                        <div
                                class="img-box"
                                element-loading-text="拼命加载中"
                                element-loading-spinner="el-icon-loading"
                                @click="unselect(item, index)"
                                v-for="(item, index) in selectedUsers"
                                :key="index">
                            <el-image
                                    class="thumb-item-img"
                                    :src="item.user.avatar"
                                    fit="cover"
                                    style="width: 100%; height: 100%;">
                            </el-image>

                            <div class="control">
                                <div class="preview selected">
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
                                @click="selectOne(item, index)"
                                v-for="(item, index) in activityUsers"
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
    data() {
      return {
        searchLoading: false,
        refreshPagination: true, // 页数增加的时候，因为缓存的缘故，需要刷新Pagination组件
        currentPage: 1, // 默认获取第一页的数据
        pageCount: 50, // 每页100条数据
        total_nums: 0, // 分组内的用户总数
        formInline: {
          activity_id: '',
          name: '',
          fake: null
        },
        fakeArr: [
          {
            value: null,
            label: "全部"
          },
          {
            value: 0,
            label: "假数据"
          },
          {
            value: 1,
            label: "真用户"
          },
        ],
        levelArr: [
          {
            value: 1,
            label: "一等奖"
          },
          {
            value: 2,
            label: "二等奖"
          }
        ],
        level: '',
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
        activityArr: [],
        activityUsers: [],
        selectedUsers:[]
      }
    },
    methods: {
      async getUser(activity_id, num, page) {
        let count = num || this.pageCount
        let p = page || this.currentPage
        try {
          const usersData = await Activity.getActivityUsers(activity_id, count, p)
          console.log(usersData)
          this.activityUsers = usersData.data
          this.total_nums = usersData.total
        } catch (error) {
          console.log(error)
        }
      },
      async searchActivity(query) {
        if (query) {
          try {
            const activity = await Activity.searchActivity(50, 1, query)
            console.log(activity)
            this.activityArr = activity.data
            this.selectedUsers = []
          } catch (error) {
            if (error.error_code === 10020) {
              this.tableData = []
            }
          }
        }
      },
      async searchActivityUser() {
        try {
          const activity = await Activity.getActivityUsers(
            this.formInline.activity_id, this.pageCount, this.currentPage, this.formInline.name, this.formInline.fake)
          console.log(activity)
          this.activityUsers = activity.data
          this.currentPage = activity.current_page
          this.total_nums = activity.total
        } catch (error) {
          if (error.error_code === 10020) {
            this.tableData = []
          }
        }
      },
      selectOne(item, index) {
        let ind = this.user_ids.indexOf(item.user.id)
        if (ind === -1) {
          this.activityUsers.splice(index, 1)
          this.selectedUsers.push(item)
          this.user_ids.push(item.user.id)
        }
        console.log(this.user_ids)
        console.log(this.selectedUsers)
      },
      unselect(item, index) {
        let ind = this.user_ids.indexOf(item.user.id)
        this.activityUsers.push(item)
        this.selectedUsers.splice(index, 1)
        this.user_ids.splice(ind, 1)
        console.log(this.user_ids)
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.searchActivityUser()
      },
      changeActivity(val){
        console.log(val)
        if (val) {
          this.getUser(val, this.pageCount, this.currentPage)
        }
      },
      async save(){
        try {
            if (this.beforeSubmitHandler()) {
              let data = {user_ids: this.user_ids, level: this.level}
              let res = await Activity.addActivityWinners(this.formInline.activity_id, data)
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
        this.user_ids = []
        this.formInline.activity_id = ''
        this.formInline.fake = null
        this.currentPage = 1
        this.level = ''
        this.activityUsers = [];
        this.selectedUsers = [];
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

        if (!this.level) {
          this.level = 1
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
                    background-color: rgba(87, 84, 86, 0.72);
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
