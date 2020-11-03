<template>
  <div>
    <!-- 列表页面 -->
    <div class="container">
      <div class="header">
        <div class="title">红人活动列表</div>
        <el-button type="primary" plain @click="bindActivity(true)">绑定红人活动</el-button>
      </div>
      <!-- 表格 -->
      <lin-table
        :tableColumn="tableColumn"
        :tableData="tableData"
        :operate="operate"
        @handleEdit="handleEdit"
        v-loading="loading"></lin-table>

      <div class="pagination" >
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
    <!-- 编辑页面 -->
    <el-dialog :visible.sync="dialogVisible">
      <el-form
              :rules="bindRules"
              :model="form"
              status-icon
              ref="form"
              label-width="100px"
              @submit.native.prevent>
        <el-form-item label="红人" prop="influ_id">
          <el-select v-model="form.influ_id"
                     filterable
                     clearable
                     remote
                     :remote-method="searchInflu"
                     reserve-keyword
                     placeholder="请输入关键词查找"
          >
            <el-option
                    v-for="item in influUsers"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="活动" prop="activity_id">
          <el-select v-model="form.activity_id"
                     filterable
                     clearable
                     remote
                     :remote-method="searchInfluActivity"
                     reserve-keyword
                     placeholder="请输入关键词查找"
          >
            <el-option
                    v-for="item in influActivity"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item class="submit">
          <el-button type="primary" @click="submitForm('form')">保 存</el-button>
          <el-button type="default outline" @click="bindActivity(false)">取 消</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import influ from '@/models/influencer'
import activity from '@/models/activity'
import LinTable from '@/components/base/table/lin-table'

export default {
  components: {
    LinTable
  },
  data() {
    return {
      form: {
        influ_id: null,
        activity_id: null
      },
      influUsers: [],
      influActivity: [],
      dialogVisible: false,
      bindRules: {
        influ_id: [
          {
            required: true,
            message: '请选择一个红人',
            trigger: 'blur'
          }
        ],
        activity_id: [
          {
            required: true,
            message: '请选择一个活动',
            trigger: 'blur'
          }
        ]
      },
      tableColumn: [
        {
          prop: 'name',
          label: '红人名称',
          formatter: (row, column, cellValue) => row.influencer.name
        },
        { prop: 'nickname', label: '红人昵称', formatter: (row, column, cellValue) => row.influencer.nickname },
        { prop: 'title', label: '活动名称' },
        {
          prop: 'status',
          label: '状态',
          formatter: (row, column, cellValue) => (cellValue === 1 ? '显示' : '隐藏')
        }
      ],
      tableData: [],
      operate: [],
      // 分页相关
      refreshPagination: true, // 页数增加的时候，因为缓存的缘故，需要刷新Pagination组件
      currentPage: 1, // 默认获取第一页的数据
      pageCount: 10, // 每页10条数据
      total_nums: 0, // 分组内的用户总数
      loading: false
    }
  },
  async created() {
    this.loading = true
    this.getInflusActivity()
    this.operate = [
      {
        name: '编辑',
        func: 'handleEdit',
        type: 'primary'
      },
      {
        name: '隐藏',
        func: 'handleHide',
        type: 'danger',
        auth: '隐藏活动'
      }
    ]
    this.loading = false
  },
  methods: {
    async getInflusActivity() {
      try {
        const influs = await influ.getbindInfluActivity(this.pageCount, this.currentPage)
        this.tableData = influs.data
        this.total_nums = influs.total
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    handleEdit(val) {
      this.form.influ_id = val.row.influ_id
      this.form.activity_id = val.row.id
      this.influActivity.push({ label: val.row.title, value: val.row.id })
      this.influUsers.push({ label: val.row.influencer.name, value: val.row.influ_id })
      this.bindActivity(true)
    },
    handleHide(val) {
      this.$confirm('确认隐藏当前活动，前台将无法访问, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        const res = await activity.hideActivity(val.row.id)
        if (res.error_code === 0) {
          this.getInflusActivity(this.pageCount, this.currentPage)
          this.$message({
            type: 'success',
            message: `${res.msg}`
          })
        }
      })
    },
    // 切换分页
    async handleCurrentChange(val) {
      this.currentPage = val
      // this.loading = true
      // setTimeout(() => {
      //   // this._getTableData(
      //   //   (this.currentPage - 1) * this.pageCount,
      //   //   this.pageCount,
      //   // )
      //   this.loading = false
      // }, 100)
      this.getInflus(this.pageCount, this.currentPage)
    },
    async searchInflu(value) {
      if (value) {
        const influList = await influ.searchInfluencer(value)
        if (influList) {
          influList.forEach((item) => {
            item.label = item.name
            item.value = item.id
          })
          this.influUsers = influList
        }
      }
    },
    async searchInfluActivity(value) {
      if (value) {
        const privateActivityList = await influ.searchPrivateActivity(value)
        if (privateActivityList) {
          privateActivityList.forEach((item) => {
            item.label = item.title
            item.value = item.id
          })
          this.influActivity = privateActivityList
        }
      }
    },
    bindActivity(val) {
      if (val === false) {
        this.resetForm('form')
      }
      this.dialogVisible = val
    },
    resetForm(formName) {
      this.$refs[formName].resetFields()
    },
    async submitForm(formName) {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }

      try {
        const res = await influ.bindInfluActivity(this.form)
        if (res.error_code === 0) {
          this.$message.success(`${res.msg}`)
          this.resetForm(formName)
          this.bindActivity(false)
        }
      } catch (error) {
        console.log(error)
        this.$message.error(`${error.toString()}`)
      }
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

  .pagination {
    display: flex;
    justify-content: flex-end;
    margin: 20px;
  }
}
</style>
