<template>
  <div>
    <!-- 列表页面 -->
    <div class="container" v-if="!showEdit">
      <div class="header">
        <div class="title">奖品列表</div>
        <router-link tag="a" to="/prize/add"><el-button type="primary" plain>添加奖品</el-button></router-link>
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
    <prize-edit v-else @editClose="editClose" :editPrizeID="editPrizeID"></prize-edit>
  </div>
</template>

<script>
import moment from 'moment'
import prize from '@/models/prize'
import LinTable from '@/components/base/table/lin-table'
import PrizeEdit from './PrizeEdit'

export default {
  components: {
    PrizeEdit,
    LinTable
  },
  data() {
    return {
      tableColumn: [
        { prop: 'name', label: '奖品名称' },
        {
          prop: 'main_img_url',
          label: '缩略图',
          formatter: (row, column, cellValue) => `<img src="${cellValue}" alt="${row.name}">`
        },
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
      editPrizeID: 1,
      loading: false
    }
  },
  async created() {
    this.loading = true
    this.getPrizes()
    this.operate = [
      {
        name: '编辑',
        func: 'handleEdit',
        type: 'primary'
      },
      {
        name: '删除',
        func: 'handleDelete',
        type: 'danger',
        auth: '删除图书'
      }
    ]
    this.loading = false
  },
  methods: {
    async getPrizes() {
      try {
        const prizes = await prize.getPrizes(this.pageCount, this.currentPage)
        this.tableData = prizes.data
        this.total_nums = prizes.total
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    handleEdit(val) {
      console.log(val)
      this.showEdit = true
      this.editPrizeID = val.row.id
    },
    handleDelete(val) {
      this.$confirm('此操作将永久删除该项, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        const res = await prize.deletePrize(val.row.id)
        if (res.error_code === 0) {
          this.getPrizes()
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
      this.getPrizes(this.pageCount, this.currentPage)
    },
    rowClick() {

    },
    editClose() {
      this.showEdit = false
      this.getPrizes()
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
