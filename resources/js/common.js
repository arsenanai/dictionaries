export const common = {
	data(){
		return {
			tokenVariable: 'enstru_token',
			appLanguage: 'enstru_language',
		}
	},
	methods:{
		display(what,target){
			var result = ''
			if(target!==null)
		      if(what==='name')
		          result = target['name_'+this.$i18n.locale]
		      else if(what==='description')
		          result = target['description_'+this.$i18n.locale]
		    if(result.length>100)
		    	result = result.substring(0,75)+' ... '+result.substring(result.length-25,result.length)
		   	return result
	    },
	    logout(){
	    	this.$router.push({name:'auth.logout'})
	    },
	    basicErrorHandling(e){
	    	console.log(e)
	    	if(e.response)
	    		if(e.response.status)
            		if(e.response.status==401)
                		this.logout()
	    },
	    getType(type){
            if(type.startsWith('migrate_'))
                return type.split('_')[1]
            else if(type.startsWith('code_')
            		||type.startsWith('subgroup_')
            		||type.startsWith('group_')
            	)
                return type.split('_')[0]
            else
                return type
        },
        stringIsSet(string){
        	return (string!=null && string != '')
        },
        arrayIsSet(array){
        	return (array!=null && array.length>0)
        },
	}
}