<template>
    <div class="login">
        <el-card>
            <h2>SURPRIZE</h2>
            <el-form
                    class="login-form"
                    :model="model"
                    :rules="rules"
                    ref="form"
                    @submit.native.prevent="login"
            >
                <el-form-item prop="username">
                    <el-input
                            v-model="model.username"
                            placeholder="Username"
                            prefix-icon="el-icon-user"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item prop="password">
                    <el-input
                            v-model="model.password"
                            placeholder="Password"
                            type="password"
                            prefix-icon="el-icon-lock"
                    >
                    </el-input>
                </el-form-item>
                <el-form-item>
                    <el-button
                            :loading="loading"
                            class="login-button"
                            type="primary"
                            native-type="submit"
                            block
                    >Login
                    </el-button
                    >
                </el-form-item>
                <a class="forgot-password" href="#">Forgot password ?</a>
            </el-form>
        </el-card>
        <!--<canvas id="particle-canvas"></canvas>-->
    </div>
</template>

<script>
import { mapActions, mapMutations } from 'vuex'
import User from '@/lin/models/user'
import Utils from '@/lin/utils/util'

export default {
  name: 'login',
  data() {
    return {
      validCredentials: {
        username: 'lightscope',
        password: 'lightscope'
      },
      wait: 2000, // 2000ms之内不能重复发起请求
      throttleLogin: null, // 节流登录
      model: {
        username: '',
        password: ''
      },
      loading: false,
      rules: {
        username: [
          {
            required: true,
            message: 'Username is required',
            trigger: 'blur'
          },
          {
            min: 5,
            message: 'Username length should be at least 5 characters',
            trigger: 'blur'
          }
        ],
        password: [
          {
            required: true,
            message: 'Password is required',
            trigger: 'blur'
          },
          {
            min: 6,
            message: 'Password length should be at least 6 characters',
            trigger: 'blur'
          }
        ]
      }
    }
  },
  methods: {
    async login() {
      const valid = await this.$refs.form.validate()
      if (!valid) {
        return
      }
      this.loading = true
      await this.simulateLogin()
      this.loading = false
    },
    async simulateLogin() {
      const { username, password } = this.model
      try {
        this.loading = true
        await User.getToken(username, password)
        await this.getInformation()
        this.loading = false
        this.$router.push('/home')
        this.$message.success('登录成功')
      } catch (e) {
        this.loading = false
        console.log(e)
      }
    },
    async getInformation() {
      try {
        // 尝试获取当前用户信息
        const user = await User.getAuths()
        this.setUserAndState(user)
        this.setUserAuths(user.auths)
      } catch (e) {
        console.log(e)
      }
    },
    ...mapActions(['setUserAndState']),
    ...mapMutations({
      setUserAuths: 'SET_USER_AUTHS'
    })
  },
  created() {
    // 节流登录
    this.throttleLogin = Utils.throttle(this.login, this.wait)
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
    .login {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        /*margin-top: 10%;*/
    }

    .login-button {
        width: 100%;
        margin-top: 40px;
    }

    .login-form {
        width: 290px;
    }

    .forgot-password {
        margin-top: 10px;
    }
</style>
<style lang="scss">
    $teal: #1b2c5f;
    .el-button--primary {
        background: $teal;
        border-color: $teal;

        &:hover,
        &.active,
        &:focus {
            background: lighten($teal, 7);
            border-color: lighten($teal, 7);
        }
    }

    .login .el-input__inner:hover {
        border-color: $teal;
    }

    .login .el-input__prefix {
        background: rgb(238, 237, 234);
        /*left: 0;*/
        height: calc(100% - 2px);
        left: 1px;
        top: 1px;
        border-radius: 3px;

        .el-input__icon {
            width: 30px;
        }
    }

    .login .el-input input {
        padding-left: 35px;
    }

    .login .el-card {
        padding-top: 0;
        padding-bottom: 30px;
    }

    h2 {
        letter-spacing: 1px;
        padding-bottom: 20px;
        font-weight: bold;
    }

    a {
        color: $teal;
        text-decoration: none;

        &:hover,
        &:active,
        &:focus {
            color: lighten($teal, 7);
        }
    }

    .login .el-card {
        width: 340px;
        display: flex;
        justify-content: center;
    }

    #app {
        font-family: Roboto, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        text-align: center;
        color: #2c3e50;
        margin: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100vh;
        background: linear-gradient(to bottom, rgb(26, 43, 96) 0%,rgb(26, 43, 96) 100%);
        background-size: contain;
    }

</style>
