<template>
  <div>
    <!-- 列表页面 -->
    <div class="container" v-if="!showEdit">
      <div class="header">
        <div class="title">赞助商列表</div>
        <router-link tag="a" to="/sponsor/add"><el-button type="primary" plain>添加赞助商</el-button></router-link>
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
    <sponsor-edit v-else @editClose="editClose" :editSponsorID="editSponsorID"></sponsor-edit>
  </div>
</template>

<script>
import moment from 'moment'
import Sponsor from '@/models/sponsor'
import LinTable from '@/components/base/table/lin-table'
import SponsorEdit from './SponsorEdit'

export default {
  components: {
    SponsorEdit,
    LinTable
  },
  data() {
    return {
      tableColumn: [
        { prop: 'name', label: '奖品名称' },
        {
          prop: 'create_time',
          label: '创建时间',
          formatter: (row, column, cellValue) => this.dateFormatter(row, column, cellValue)
        },
        {
          prop: 'update_time',
          label: '更新时间',
          formatter: (row, column, cellValue) => this.dateFormatter(row, column, cellValue)
        }
      ],
      tableData: [],
      searchKeyword: '',
      operate: [],
      showEdit: false,
      // 分页相关
      refreshPagination: true, // 页数增加的时候，因为缓存的缘故，需要刷新Pagination组件
      currentPage: 1, // 默认获取第一页的数据
      pageCount: 10, // 每页10条数据
      total_nums: 0, // 分组内的用户总数
      editSponsorID: 1
    }
  },
  async created() {
    this.loading = true
    this.getSponsors()
    this.operate = [{ name: '编辑', func: 'handleEdit', type: 'primary' }, {
      name: '删除',
      func: 'handleDelete',
      type: 'danger',
      auth: '删除赞助商'
    }]
    this.loading = false
  },
  methods: {
    async getSponsors() {
      try {
        const sponsors = await Sponsor.getSponsors(this.pageCount, this.currentPage)
        this.tableData = sponsors.data
        this.total_nums = sponsors.total
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    handleEdit(val) {
      console.log(val)
      this.showEdit = true
      this.editSponsorID = val.row.id
      console.log(this.editSponsorID)
    },
    handleDelete(val) {
      this.$confirm('此操作将永久删除该项, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        const res = await Sponsor.deleteSponsor(val.row.id)
        if (res.error_code === 0) {
          this.getSponsors()
          this.$message({
            type: 'success',
            message: `${res.msg}`
          })
        }
      })
    },
    dateFormatter(row, column, cellValue) {
      if (cellValue) {
        return moment(cellValue).format('YYYY-MM-DD HH:mm:ss')
      }
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
      this.getSponsors()
    },
    rowClick() {

    },
    editClose() {
      this.showEdit = false
      this.getSponsors()
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
