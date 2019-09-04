import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

const messages = {
	"kk": {
		login: {
			page_title: 'Жүйеге кіру'
		},
		logout: {
			message: 'Жүйеден шығуда...'
		},
		'Logout': 'Шығу'
	},
	"ru": {
		login: {
			page_title: 'Войти в систему'
		},
		logout: {
			message: 'Выход из системы...'
		},
		'Logout': 'Выйти'
	},
}

export const i18n = new VueI18n({
  	locale: 'kk', 
	fallbackLocale: 'kk',
	silentTranslationWarn: true,
  	messages: messages, // set locale messages
})