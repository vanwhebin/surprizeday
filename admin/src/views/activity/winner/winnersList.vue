<template>
    <div>
        <div class="container">
            <div class="header">
                <div class="title">Winners列表</div>
            </div>
            <lin-table
                    :tableColumn="tableColumn"
                    :tableData="tableData"
                    :operate="operate"
                    @handleDelete="handleDelete"
                    v-loading="loading"></lin-table>
            <div class="pagination" v-if="tableData">
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
    </div>
</template>

<script>
  import moment from 'moment'
  import User from '@/models/users'
  import LinTable from '@/components/base/table/lin-table'

  export default {
    components: {
      LinTable
    },
    data() {
      return {
        tableColumn: [
          { prop: 'title', label: '活动标题', formatter: (row, column, cellValue) => row.activity.title },
          { prop: 'name', label: '名称' },
          { prop: 'start_time', label: '开奖时间', formatter: (row, column, cellValue) => this.dateFormatter(row, column, cellValue) },
          { prop: 'level', label: '级别' }
        ],
        tableData: [],
        operate: [],
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
      this.getWinners(this.pageCount, this.currentPage)
      this.operate = [{
        name: '删除',
        func: 'handleDelete',
        type: 'danger',
        auth: '删除活动'
      }]
      this.loading = false
    },
    methods: {
      async getWinners(num, page) {
        try {
          const winnerData = await User.getWinners(num, page)
          console.log(winnerData)
          this.tableData = winnerData.data
          this.total_nums = winnerData.total
        } catch (error) {
          if (error.error_code === 10020) {
            this.tableData = []
          }
        }
      },
      handleDelete(val) {
        this.$confirm('此操作将删除该活动Winner, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(async () => {
          const res = await User.deleteWinners(val.row.id)
          if (res.error_code === 0) {
            this.getWinners(this.pageCount, this.currentPage)
            this.$message({
              type: 'success',
              message: `${res.msg}`
            })
          }
        })
      },
      dateFormatter(row, column, cellValue) {
        if (row) {
          return moment(row.activity.start_time * 1000).format('YYYY-MM-DD HH:mm:ss')
        }
      },
      handleCurrentChange(val) {
        this.currentPage = val
        this.loading = true
        this.getWinners(this.pageCount, this.currentPage)
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
