<template>
  <div>
    <!-- 列表页面 -->
    <div class="container" v-if="!showEdit">
      <div class="header">
        <div class="title">活动列表</div>
        <router-link tag="a" to="/activity/add"><el-button type="primary" plain>添加活动</el-button></router-link>
      </div>
      <!-- 表格 -->
      <lin-table
        :tableColumn="tableColumn"
        :tableData="tableData"
        :operate="operate"
        @handleEdit="handleEdit"
        @handleDelete="handleDelete"
        @row-click="rowClick"
        v-loading="loading"></lin-table>
      <div class="pagination" v-if="!searchKeyword">
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
    <activity-edit v-else @editClose="editClose" :editActivityID="editActivityID"></activity-edit>

  </div>
</template>

<script>
import moment from 'moment'
import FieldsState from '@/config/fields-meaning'
import Activity from '@/models/activity'
import LinTable from '@/components/base/table/lin-table'
import ActivityEdit from './ActivityEdit'

export default {
  components: {
    LinTable,
    ActivityEdit
  },
  data() {
    return {
      tableColumn: [
        { prop: 'title', label: '活动标题' },
        { prop: 'activity_img_id', label: '封面', formatter: (row, column, cellValue) => `<img src="${cellValue}" alt="${row.name}">` },
        { prop: 'start_time', label: '开奖时间', formatter: (row, column, cellValue) => this.dateFormatter(row, column, cellValue) },
        { prop: 'order', label: '排序' },
        { prop: 'type', label: '活动类别', formatter: (row, column, cellValue) => FieldsState.activity_type[cellValue] },
        { prop: 'update_time', label: '更新时间' },
        { prop: 'status', label: '状态', formatter: (row, column, cellValue) => FieldsState.activity_status[cellValue] }
      ],
      tableData: [],
      searchKeyword: '',
      operate: [],
      showEdit: false,
      // 分页相关
      refreshPagination: true, // 页数增加的时候，因为缓存的缘故，需要刷新Pagination组件
      currentPage: 1, // 默认获取第一页的数据
      pageCount: 10, // 每页15条数据
      total_nums: 0, // 分组内的用户总数
      editActivityID: 1
    }
  },
  async created() {
    this.loading = true
    this.getActivitys(this.pageCount, this.currentPage)
    this.operate = [{ name: '编辑', func: 'handleEdit', type: 'primary' }, {
      name: '删除',
      func: 'handleDelete',
      type: 'danger',
      auth: '删除活动'
    }]
    this.loading = false
  },
  methods: {
    async getActivitys(num, page) {
      try {
        const activityData = await Activity.getActivitys(num, page)
        console.log(activityData)
        this.tableData = activityData.data
        this.total_nums = activityData.total
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    handleEdit(val) {
      console.log('val', val)
      this.showEdit = true
      this.editActivityID = val.row.id
    },
    handleDelete(val) {
      this.$confirm('此操作将删除该活动, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        const res = await Activity.deleteActivity(val.row.id)
        if (res.error_code === 0) {
          this.getActivitys()
          this.$message({
            type: 'success',
            message: `${res.msg}`
          })
        }
      })
    },
    rowClick() {

    },
    editClose() {
      this.showEdit = false
      this.getActivitys()
    },
    dateFormatter(row, column, cellValue) {
      if (cellValue) {
        return moment(cellValue * 1000).format('YYYY-MM-DD HH:mm:ss')
      }
    },
    handleCurrentChange(val) {
      this.currentPage = val
      this.loading = true
      this.getActivitys(this.pageCount, this.currentPage)
      this.loading = false
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
