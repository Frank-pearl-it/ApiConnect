<template>
  <q-page style="padding: 0" class="q-pa-md window-height window-width row justify-center items-center" id="container">
    <div id="back">
      <canvas id="canvas" class="canvas-back"></canvas>
      <div class="backRight"></div>
      <div class="backLeft"></div>
    </div>

    <div id="slideBox">
      <div class="topLayer">
        <div class="left hide-on-mobile"></div> <!-- Hide left on mobile -->
        <div class="right">
         <div id="image">
            <img src="../assets/logo.svg" alt="Houvast logo" class="logo" width="675" style="margin-bottom: -100px;" />
          </div>

          <div class="content">
            <q-form :greedy="true" @submit.prevent="resetPassword()">
              <div>
                <h2><b>Nieuw wachtwoord</b></h2>

                <!-- Password Input -->
                <div ref="passwordInput" class="form-element form-stack">
                  <q-input :type="isPwd ? 'password' : 'text'" id="password" label="Wachtwoord" :rules="[
                    (val) => (val && val.length > 0) || 'Dit veld mag niet leeg zijn.',
                    (val) => (val && val.length >= 8) || 'Wachtwoord moet minimaal 8 tekens bevatten.',
                  ]" v-model="formData.password" autofocus>
                    <template v-slot:append>
                      <q-icon :name="isPwd ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwd = !isPwd"></q-icon>
                    </template>
                  </q-input>
                </div>

                <!-- Confirm Password Input -->
                <div class="form-element form-stack">
                  <q-input :type="isPwdConfirm ? 'password' : 'text'" id="password-confirm" label="Bevestig wachtwoord"
                    :rules="[
                      (val) => val === formData.password || 'Wachtwoorden komen niet overeen.',
                      (val) => (val && val.length >= 8) || 'Wachtwoord moet minimaal 8 tekens bevatten.',
                    ]" v-model="formData.password_confirmation">
                    <template v-slot:append>
                      <q-icon :name="isPwdConfirm ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                        @click="isPwdConfirm = !isPwdConfirm"></q-icon>
                    </template>
                  </q-input>
                </div>
              </div>

              <div class="form-element form-submit">
                <q-btn icon-right="arrow_forward" :loading="this.loginLoading" class="login" type="submit"
                  label="Verder">
                </q-btn>
              </div>
            </q-form>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent, ref, nextTick } from "vue";
import { get, post } from "../../../resources/js/bootstrap.js";
import { popup, initializeCanvas } from "src/boot/custom-mixin.js";

import { useRoute } from "vue-router";

export default defineComponent({
  name: "ResetPasswordPage",
  data() {
    return {
      formData: {},
      isPwd: true,
      isPwdConfirm: true,
      loginLoading: false,
      passwordNeedsReset: false,
    };
  },
  mounted() {
    initializeCanvas();
    const route = useRoute();
    this.formData.token = route.query.token || "";
    this.formData.email = route.query.email || "";
  },
  beforeMount() { },
  methods: {
    resetPassword() {
      this.loginLoading = true;
      if (!this.formData.token || !this.formData.email) { 
        this.loginLoading = false;
        popup({
          status: 400,
          data: { message: "Ongeldige reset-link." }
        });
        return;
      }

      post("resetPassword", this.formData)
        .then((response) => {
          popup(response);
          this.$router.push({ name: "login" });
        })
        .catch((error) => {
          popup(error.response);
        })
        .finally(() => {
          this.loginLoading = false;
        });
    },
  },
});
</script>

<style scoped lang="scss">
$theme-signup: var(--q-primary);
$theme-signup-darken: var(--q-primary);
$theme-signup-background: #2C3034;
$theme-login: var(--q-primary);
$theme-login-darken: var(--q-primary);
$theme-login-background: white;
$theme-dark: #212121;
$theme-light: white;
$font-default: 'Roboto', sans-serif;

$success: #5cb85c;
$error: #d9534f;

.centerContent {
  display: flex;
  justify-content: center;
  align-items: center;
}

.text-container {
  height: 8vh;
  font-size: 22px;
  letter-spacing: 0.2rem;
  color: white;
}

/* Mobile adjustments */
@media only screen and (max-width: 1023px) {

  .signup-info,
  .login-info {
    display: none;
  }

  #slideBox {
    width: 100% !important;
    margin-left: 0 !important;
  }
}

#image {
  height: 35%;
  display: flex;
  justify-content: center;
  /* Centers horizontally */
  align-items: center;
  /* Centers vertically */
}

body {
  margin: 0;
  height: 100%;
  overflow: hidden;
  width: 100% !important;
  box-sizing: border-box;
  font-family: $font-default;
}

.backRight {
  position: absolute;
  right: 0;
  width: 50%;
  height: 100%;
  background: $theme-signup;
}

.backLeft {
  position: absolute;
  left: 0;
  width: 50%;
  height: 100%;
  background: $theme-login;
}

#back {
  width: 100%;
  height: 100%;
  position: absolute;
  z-index: -999;
}

.canvas-back {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10;
}

#slideBox {
  width: 50%;
  max-height: 100%;
  height: 100%;
  overflow: hidden;
  margin-left: 50%;
  position: absolute;
  box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
}

.topLayer {
  width: 200%;
  height: 100%;
  position: relative;
  left: 0;
  left: -100%;
}

label {
  font-size: 0.8em;
}

input {
  background-color: transparent;
  border: 0;
  outline: 0;
  padding: 8px 1px;
  margin-top: 0.1em;
}

.left {
  width: 50%;
  height: 100%;
  overflow: scroll;
  background: $theme-signup-background;
  left: 0;
  position: absolute;

  label {
    color: $theme-light;
  }

  input {
    border-bottom: 1px solid $theme-light;
    color: $theme-light;

    &:focus,
    &:active {
      border-color: $theme-signup;
      color: $theme-signup;
    }

    &:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 30px $theme-signup-background inset;
      -webkit-text-fill-color: $theme-light;
    }
  }

  a {
    color: $theme-signup;
  }
}

.right {
  width: 50%;
  height: 100%;
  overflow: hidden;
  background: $theme-login-background;
  right: 0;
  position: absolute;

  label {
    color: $theme-dark;
  }

  input {
    border-bottom: 1px solid $theme-dark;

    &:focus,
    &:active {
      border-color: $theme-login;
    }

    &:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 30px $theme-login-background inset;
      -webkit-text-fill-color: $theme-dark;
    }
  }
}

.content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 65%;
  width: 80%;
  margin: 0 auto;
  position: relative;
}

.content h2 {
  font-weight: 300;
  font-size: 2.6em;
  margin: 0.2em 0 0.1em;
}

.left .content h2 {
  color: $theme-signup;
}

.right .content h2 {
  color: $theme-login;
}

.form-element {
  margin: 1.6em 0;

  &.form-submit {
    margin: 1.6em 0 0;
  }
}

.form-stack {
  display: flex;
  flex-direction: column;
}

.checkbox {
  -webkit-appearance: none;
  outline: none;
  background-color: $theme-light;
  border: 1px solid $theme-light;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05);
  padding: 12px;
  border-radius: 4px;
  display: inline-block;
  position: relative;
}

.checkbox:focus,
.checkbox:checked:focus,
.checkbox:active,
.checkbox:checked:active {
  border-color: $theme-signup;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px 1px 3px rgba(0, 0, 0, 0.1);
}

.checkbox:checked {
  outline: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05), inset 0px -15px 10px -12px rgba(0, 0, 0, 0.05), inset 15px 10px -12px rgba(255, 255, 255, 0.1);
}

.checkbox:checked:after {
  outline: none;
  content: '\2713';
  color: $theme-signup;
  font-size: 1.4em;
  font-weight: 900;
  position: absolute;
  top: -4px;
  left: 4px;
}

.form-checkbox {
  display: flex;
  align-items: center;

  label {
    margin: 0 6px 0;
    font-size: 0.72em;
  }
}

button {
  padding: 0.8em 1.2em;
  margin: 0 10px 0 0;
  width: auto;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 1em;
  color: #fff;
  line-height: 1em;
  letter-spacing: 0.6px;
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);
  border: 0;
  outline: 0;
  transition: all 0.25s;

  &.signup {
    background: $theme-signup;
  }

  &.login {
    background: $theme-login;
  }

  &.off {
    background: none;
    box-shadow: none;
    margin: 0;

    &.signup {
      color: $theme-signup;
    }

    &.login {
      color: $theme-login;
    }
  }
}

button:focus,
button:active,
button:hover {
  box-shadow: 0 4px 7px rgba(0, 0, 0, 0.1), 0 3px 6px rgba(0, 0, 0, 0.1);

  &.signup {
    background: $theme-signup-darken;
  }

  &.login {
    background: $theme-login-darken;
  }

  &.off {
    box-shadow: none;

    &.signup {
      color: $theme-signup;
      background: $theme-dark;
    }

    &.login {
      color: $theme-login-darken;
      background: $theme-light;
    }
  }
}
</style>