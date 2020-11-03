<template>
  <div>
    <!-- 列表页面 -->
    <div class="container" v-if="!showEdit">
      <div class="header">
        <div class="title">红人列表</div>
        <router-link tag="a" to="/influencer/add"><el-button type="primary" plain>添加红人</el-button></router-link>
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
    <influ-edit v-else @editClose="editClose" :editinfluID="editinfluID"></influ-edit>
  </div>
</template>

<script>
import moment from 'moment'
import influ from '@/models/influencer'
import LinTable from '@/components/base/table/lin-table'
import InfluEdit from './influEdit'

export default {
  components: {
    InfluEdit,
    LinTable
  },
  data() {
    return {
      tableColumn: [
        { prop: 'id', label: 'ID', width: 50 },
        { prop: 'name', label: '红人名称' },
        {
          prop: 'platform',
          label: '平台',
          formatter: (row, column, cellValue) => this.platformFormat(row, column, cellValue)
        },
        {
          prop: 'email',
          label: '邮箱'
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
      editinfluID: 1,
      loading: false
    }
  },
  async created() {
    this.loading = true
    this.getInflus()
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
        auth: '删除红人'
      }
    ]
    this.loading = false
  },
  methods: {
    async getInflus() {
      try {
        const influs = await influ.getInfluencers(this.pageCount, this.currentPage)
        this.tableData = influs.data
        this.total_nums = influs.total
      } catch (error) {
        if (error.error_code === 10020) {
          this.tableData = []
        }
      }
    },
    handleEdit(val) {
      this.showEdit = true
      this.editinfluID = val.row.id
    },
    handleDelete(val) {
      this.$confirm('此操作将永久删除该项, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(async () => {
        const res = await influ.deleteInfluencer(val.row.id)
        if (res.error_code === 0) {
          this.getInflus(this.pageCount, this.currentPage)
          this.$message({
            type: 'success',
            message: `${res.msg}`
          })
        }
      })
    },
    platformFormat(row, column, cellValue) {
      let t = ''
      row.relationship.forEach((item) => {
        t += ` ${item.source.name}`
      })
      return t
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
      this.getInflus(this.pageCount, this.currentPage)
    },
    rowClick() {

    },
    editClose() {
      this.showEdit = false
      this.getInflus()
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
