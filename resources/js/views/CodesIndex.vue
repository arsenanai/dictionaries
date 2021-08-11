<template>
    <div class="container">
        <h2>
            {{$t('Codes')}}
            <router-link v-if=false class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>
        </h2>
        <hr>
        <div class="row">
            <div class="col">
                <form class="form-inline">
                    <label class="sr-only" for="name">{{$t('Group')}}</label>
                    <select class="form-control mb-2 mr-sm-2" style="max-width:200px;" v-model="queries.group_id" @change="onFilterChanged('group',queries.group_id,null)">
                        <option value=-1>{{$t('Group')}}</option>
                        <option v-for="group in groups" :value="group.id">
                            {{view('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                        </option>
                    </select>
                    <label class="sr-only" for="name">{{$t('Subgroup')}}</label>
                    <select class="form-control mb-2 mr-sm-2" style="max-width:200px;" v-model="queries.subgroup_id" @change="filterChanged=true" :disabled="queries.group_id==-1"
                        >
                        <option value=-1>{{$t('Subgroup')}}</option>
                        <option v-for="subgroup in subgroups" :value="subgroup.id">
                            {{view('name',subgroup)}}
                        </option>
                    </select>
                    <div class="input-group mb-2 mr-sm-2" v-if="minified_code_codes">
                        <input type=text list="code.code" class="form-control" id="codeInput" :placeholder="$t('Code')" :aria-label="$t('Code')"
                         v-model="queries.code" @change="filterChanged=true" @keyup="type($event,'code_code')">
                        <datalist id="code.code">
                          <option v-for="item in minified_code_codes">{{item}}</option>
                        </datalist>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" @click.prevent="onClear('code')">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-group mb-2 mr-sm-2" v-if="minified_code_names">
                        <input type=text list="code.name" class="form-control" id="nameInput" :placeholder="$t('Name')" :aria-label="$t('Name')"
                         v-model="queries.name" @change="filterChanged=true" @keyup="type($event,'code_name')">
                         <datalist id="code.name">
                          <option v-for="item in minified_code_names">{{item}}</option>
                        </datalist>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" @click.prevent="onClear('name')">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-group mb-2 mr-sm-2" v-if="minified_code_descriptions">
                        <input type=text list="code.desc" class="form-control" id="descriptionInput" :placeholder="$t('Description')" :aria-label="$t('Description')"
                         v-model="queries.description" @change="filterChanged=true" @keyup="type($event,'code_description')">
                         <datalist id="code.desc">
                          <option v-for="item in minified_code_descriptions">{{item}}</option>
                        </datalist>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" @click.prevent="onClear('description')">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                     <label class="sr-only" for="zks">{{$t('ZKS')}}</label>
                    <select v-model="queries.isZKS" id=zks class="form-control mb-2 mr-sm-2" @change="filterChanged=true">
                        <option value selected>{{$t('ZKS')}} ({{$t('All')}})</option>
                        <option value="true">{{$t('Yes')}}</option>
                        <option value="false">{{$t('No')}}</option>
                    </select>
                    <label class="sr-only" for="type">{{$t('Type')}}</label>
                    <select v-model="queries.type" id=type class="form-control mb-2 mr-sm-2" @change="filterChanged=true">
                        <option value selected>{{$t('Type')}} ({{$t('All')}})</option>
                        <option value="GOODS">{{$t('GOODS')}}</option>
                        <option value="WORK">{{$t('WORK')}}</option>
                        <option value="SERVICE">{{$t('SERVICE')}}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary mb-2 mr-sm-2" @click.prevent="filter()"
                        :disabled="filterChanged===false">{{$t('Filter')}}</button>
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :class="{disabled: (selectedCodes.length===0 || (selectedAll==true && filterApplied==false))}"  
                        data-toggle="modal" data-target="#migrationModal">
                        {{$t('Migrate')}}
                    </a>
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :href="getExportLink()" target="_blank">
                        Excel
                    </a>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <nav aria-label="Page navigation example mr-3 float-right">
              <ul class="pagination justify-content-end">
                <li class="page-item">
                    <input class="page-link page-number" type="number" :value="currentPage()" v-if="meta!==null" 
                        @change="goToCustomPage($event.target.value)" min="1" max="lasPage()"/>
                </li>
                <li class="page-item" :class="{disabled: !prevPage()}">
                  <a class="page-link" href="#" @click.prevent="goToPrev"><i class="fa fa-chevron-left"></i></a>
                </li>
                <li class="page-item" :class="{disabled: !nextPage()}">
                  <a class="page-link" href="#" @click.prevent="goToNext"><i class="fa fa-chevron-right"></i></a>
                </li>
              </ul>
            </nav>
            </div>
        </div>
        <div class="row">
            <div v-if="error" class="error">
                <p>{{ error }}</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped" >
                <thead>
                    <tr>
                        <th colspan=10>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="exampleCheck1" 
                                @click="selectAll('all',$event)" v-model="selectedAll">
                                <label class="form-check-label" for="exampleCheck1">{{$t('Select All')}}</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="exampleCheck2"
                                @click="selectAll('page',$event)" v-model="selectedAllOnPage"
                                >
                                <label class="form-check-label" for="exampleCheck2">{{$t('Select All On Page')}}</label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <thead v-if="selectedAll || selectedCodes.length>0">
                    <tr>
                        <th colspan="10" class="alert-warning">
                            {{$t('Total selected')}}: {{totalSelected()}}
                        </th>
                    </tr>
                </thead>
                <thead class=""> 
                    <tr>
                        <th scope="col">
                            #
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            {{$t('Group')}}
                            <i :class="getOrder('groups')"></i>
                        </th>
                        <th scope="col" class="d-none d-md-table-cell">
                            {{$t('Subgroup')}}
                            <i :class="getOrder('subgroups')"></i>
                        </th>
                        <th scope="col" :class="{'font-italic': queries.sort==='code'}" style="cursor: pointer;"
                            @click="sortBy('code')">
                            {{$t('Code')}} 
                            <i :class="getOrder('code')"></i>
                        </th>
                        <th scope="col" :class="{'font-italic': (queries.sort==='name_kk'||queries.sort==='name_ru')}" style="cursor: pointer;"
                            @click="sortBy('name')">
                            {{$t('Name')}}
                            <i :class="getOrder('name')"></i>
                        </th>
                        <th scope="col" class="d-none d-sm-table-cell" :class="{'font-italic': (queries.sort==='description_kk'||queries.sort==='description_ru')}" style="cursor: pointer;"
                            @click="sortBy('description')">
                            {{$t('Description')}}
                            <i :class="getOrder('description')"></i>
                        </th>
                        <th scope="col" class="d-none d-sm-table-cell">{{$t('isZKS')}}</th>
                        <th scope=col class="d-none d-sm-table-cell">{{$t('Type')}}</th>
                        <th scope="col">{{$t('Last modified by')}}</th>
                        <th scope="col">
                            <span class="float-right">
                                {{(currentPage()-1)*perPage()+1}} - {{currentPage()*perPage()}} / {{total()}}
                            </span>
                        </th>
                    </tr>
                    
                </thead>
                <tbody v-if="codes!==null && codes.length>0">
                    <tr v-for="(code,index) in codes" :class="{selected: code.selected}" @click="select(code)" style="cursor: pointer;">
                        <th scope="row" >
                            <i v-if="code.selected" class="fa fa-check"></i>
                            {{ (currentPage()-1)*perPage()+index+1 }}
                        </th>
                        <td class="d-none d-md-table-cell name-cell">{{view('name',code.subgroup.group)}}</td>
                        <td class="d-none d-md-table-cell name-cell">{{view('name',code.subgroup)}}</td>
                        <td>{{ code.code }}</td>
                        <td>{{ display('name',code) }}</td>
                        <td class="d-none d-sm-table-cell">{{view('description',code)}}</td>
                        <td class="d-none d-sm-table-cell">
                            <i v-if="code.subgroup.group.isZKS" class="fa fa-check"></i>
                        </td>
                        <td class="d-none d-sm-table-cell">
                            {{$t(code.type)}}
                        </td>
                        <td>{{code.user.name}}</td>
                        <td>
                            <div class="float-right">
                                <!--<router-link class="btn btn-outline-primary btn-sm" :to="getLink('edit',code)">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button :disabled="saving" @click.prevent="onDelete(code.id)" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>-->
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="(codes!==null && codes.length===0)">
                    <tr>
                        <td class="font-italic" colspan=9>
                            {{$t('Search returned an empty result')}}
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                       <td colspan=9>
                           {{$t('Loading')}} ...
                       </td> 
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal fade" id="migrationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{$t('Migrate codes')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <form>
                            <label class="sr-only" for="migrateGroup">{{$t('Group')}}</label>
                            <select class="form-control mb-2 mr-sm-2" id="migrateGroup" v-model="migrate_group_id" @change="onFilterChanged('migrate_group',migrate_group_id)">
                                <option selected disabled value=-1>
                                    {{$t('Group')}}
                                </option>
                                <option v-for="group in migrate_groups" :value="group.id">
                                    {{view('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                                </option>
                            </select>
                            <label class="sr-only" for="migrateSubgroup">{{$t('Subgroup')}}</label>
                            <select class="form-control mb-2 mr-sm-2" id="migrateSubgroup" v-model="migrate_subgroup_id" :disabled="migrate_group_id==-1||migrate_subgroups==null">
                                <option selected disabled value=-1>
                                    {{$t('Subgroup')}}
                                </option>
                                <option v-for="subgroup in migrate_subgroups" :value="subgroup.id" :disabled="subgroup.id===queries.subgroup_id">
                                    {{view('name',subgroup)}}
                                </option>
                            </select>
                        </form>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{$t('Cancel')}}</button>
                <button type="button" class="btn btn-primary" :disabled="migrate_group_id==-1" @click.prevent="migrate()">{{$t('Migrate')}}</button>
              </div>
            </div>
          </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import api from '../api/routes';
import {common} from '../common.js'

export default {
    mixins:[common],
    data() {
        return {
            saving: false,
            codes: null,
            code_codes:null,
            code_names:null,
            code_descriptions:null,
            pageCache: 1,
            perPageCache: 15,
            meta: null,
            queries:{
                sort: null,
                order: null,
                group_id: -1,
                subgroup_id: -1,
                code: null,
                name: null,
                description: null,
                isZKS: '',
                type: '',
            },
            groups: null,
            subgroups: null,
            minified_code_codes:null,
            minified_code_names:null,
            minified_code_descriptions:null,
            migrate_groups:null,
            migrate_subgroups:null,
            migrate_group_id: -1,
            migrate_subgroup_id: -1,
            filterChanged: false,
            filterApplied: false,
            selectedCodes: [],
            selectedAll: false,
            selectedAllOnPage: false,
            choose: {
                code:false,
                name:false,
                description:false,
            },
            links: {
                first: null,
                last: null,
                next: null,
                prev: null,
            },
            error: null,
        };
    },
    mounted(){
        this.fetchData()
        this.fetchDatalist('','group')
        this.fetchDatalist('','migrate_group')
        this.fetchDatalist('','code_code')
        this.fetchDatalist('','code_name')
        this.fetchDatalist('','code_description')
    },
    watch:{
        '$route': 'fetchData'
    },
    methods: {
        fetchData(){
            this.filterApplied = false
            this.codes = this.links = this.meta = null
            this.setParams()
            this.getCodes(
                this.$route.query,
                (err, data) => {
                    this.setData(err, data);

                //next();
            });
        },
        fetchDatalist(input,type,parent=null){
            var params = {} 
              params.input = input
              params.lang = this.$i18n.locale
              params.parent = parent
              if(type==='group')
                params.onlyWithCodes=true
            else if(type==='migrate_group')
                    params.onlyWithSubgroups=true
            else if(type.includes("code_")){
                params['by'] = type.split('_')[1]
            }
            this.request(type,params)
        },
        getCodes(params, callback){
            api.all('code',{params})
                .then(response => {
                    callback(null, response.data);
                }).catch(error => {
                    callback(error, error.response);
                });
        },
        setParams(){
            var filtered = false
            for(var key in this.$route.query) {
                if(Object.keys(this.queries).includes(key)){
                    this.queries[key]=this.$route.query[key];
                    if((this.stringIsSet(this.queries[key])||this.queries[key]>-1) && !['sort','order'].includes(key))
                        filtered = true
                    if(key==='group_id' && this.queries[key]>-1)
                        this.fetchDatalist('','subgroup',this.queries[key])
                }
                
            }
            this.filterApplied = filtered
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            if(this.queries.group_id>-1 || this.queries.subgroup_id>-1 || this.queries.name || this.queries.description)
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
                if((['group_id','subgroup_id'].includes(key) && this.queries[key]>-1) ||
                (!['group_id','subgroup_id'].includes(key) && this.stringIsSet(this.queries[key])))
                    params[key] = this.queries[key]
            }
            return params;
        },
        goToNext() {
            this.$router.push({
                query: this.fillParams(this.nextPage()),
            });
        },
        goToPrev() {
            this.$router.push({
                name: 'codes.index',
                query: this.fillParams(this.prevPage()),
            });
        },
        filter(){
            this.goToCustomPage(1)
        },
        goToCustomPage(page){
            if(page>this.meta.last_page)
                page = this.meta.last_page
            this.pageCache = this.meta.current_page;
            this.perPageCache = this.meta.per_page;
            this.$router.push({
                name: 'codes.index',
                query: this.fillParams(page)
            });
        },
        setData(e, data) {
            if (e) {
                this.basicErrorHandling(e)
            } else {
                this.codes = data.data;
                this.codes.forEach((code, index) => {
                    code.selected = false
                })
                this.links = data.links;
                this.meta = data.meta;
                this.selectedCodes= []
                this.selectedAll= false
            }
        },
        getLink(which, code){
            if(which==='edit')
                return { 
                    name: 'codes.edit', 
                    params: {
                        'id': code.id 
                    }
                }

        },
        nextPage() {
            if (! this.meta || this.meta.current_page >= this.meta.last_page) {
                return;
            }
            return this.meta.current_page + 1;
        },
        prevPage() {
            if (! this.meta || this.meta.current_page <= 1) {
                return;
            }
            return this.meta.current_page - 1;
        },
        currentPage(){
            return this.meta!==null ? this.meta.current_page : this.pageCache;
        },
        lastPage(){
            return this.meta!==null ? this.meta.last_page : this.pageCache;
        },
        perPage(){
            return this.meta!==null ? this.meta.per_page : this.perPageCache;
        },
        total(){
            return this.meta!==null ? this.meta.total : this.perPageCache;
        },
        /*typeahead(input, type, except = null, parent = null, event = null){
            if (event instanceof KeyboardEvent || event === null){
                //this[type+'s'] = null
                this.filterChanged = true
                var params = {} 
                params.input = input
                params.lang = this.$i18n.locale
                params.except = except
                params.parent = parent
                const keys = Object.keys(this.queries)
                if(type!='subgroup'&&type!='group')
                    for(const key of keys){
                        if(this.queries[key]!=null && this.queries[key]!='' && ['code','name','description'].includes(key))
                            params[key] = this.queries[key]
                    }
                this.request(type,params)
            }
        },*/
        request(type,params){
            api.search(this.getType(type), params).then((response) => {
                //console.log(response.data)
                this[type+'s'] = response.data
                if(['code_name','code_code','code_description'].includes(type) && Array.isArray(response.data))
                    this['minified_'+type+'s'] = response.data.slice(0, 10);
            }).catch(e => {
                this.basicErrorHandling(e)
            });
        },
        type(event=null, type){
            if (event instanceof KeyboardEvent){
                this.filterChanged = true
                this.minified(type)
            }
        },
        minified(type){
            var result = []
            var inp = this.stringIsSet(this.queries[type.split('_')[1]]) ? this.queries[type.split('_')[1]].toLowerCase() : ''
            var limit = 10
            var count = 0
            if(this.arrayIsSet(this[type+'s']))
                for(var i=0;i<this[type+'s'].length;i++){
                    if((type!='code_code' && this[type+'s'][i].toLowerCase().includes(inp))
                        ||(type=='code_code' && this[type+'s'][i].toLowerCase().startsWith(inp))){
                        result.push(this[type+'s'][i])
                        count++
                    }
                    if(count>=limit)
                        break;
                }
            this['minified_'+type+'s'] = result
        },
        onClear(type){
            this.filterChanged = true
            this.queries[type] = null;
            this['minified_code_'+type+'s'] = this['code_'+type+'s'].slice(0, 10);
        },
        sortBy(target){
            if(this.queries.sort!=null){
                if(this.queries.sort.startsWith(target)===false){
                    this.queries.order = 'asc';
                }else
                    this.queries.order = this.queries.order === 'asc' ? 'desc' : 'asc';
            }
            if(target==='groups' || target==='subgroups'){
                this.queries.sort = target+'.name_'+this.$i18n.locale
            }else if(target==='name' || target==='description'){
                this.queries.sort = target+'_'+this.$i18n.locale
            }else if(target==='code'){
                this.queries.sort = target
            }else
                this.queries.sort = 'id';
            this.goToCustomPage(1);
        },
        getOrder(target){
            if(this.queries.sort!=null)
                if(this.queries.sort.startsWith(target)){
                    return (this.queries.order==='desc') ? 'fa fa-caret-up' : 'fa fa-caret-down'
                }else
                    return ''
        },
        /*onDelete(id) {
            if (confirm(this.$i18n.t('Are you sure that you want to delete that ')+"?")) {
                this.saving = true;
                api.delete('code',id)
               .then((response) => {
                  this.message = 'Code Deleted';
                  var toDelete = -1;
                  this.codes.every((code, i) => {
                    if(code.id===id){
                        toDelete = i
                        return false
                    }else
                        return true
                  });
                  if(toDelete!=-1)
                    this.codes.splice(toDelete, 1);
               }).catch(e => {
                this.basicErrorHandling(e)
               });
            }
        },*/
        select(code){
            code.selected = !code.selected
            if(code.selected)
                this.selectedCodes.push(code.id)
            else{
                this.selectedAll = this.selectedAllOnPage = false
                var index = this.selectedCodes.indexOf(code.id);
                if (index > -1)
                  this.selectedCodes.splice(index, 1);
            }
        },
        selectAll(type, event){
            if(this.filterApplied==false && type=="all"){
                alert(this.$i18n.t('You have to apply some filters before selecting all items'))
                event.preventDefault();
            }else{ 
                var assign = false;
                if(type=='all'){
                    assign = !this.selectedAll
                    this.selectedAllOnPage = assign
                    
                }else if(type=="page"){
                    assign = !this.selectedAllOnPage
                    this.selectedAll = false
                }
                this.selectedCodes = [];
                this.codes.forEach((code, index) => {
                    code.selected = assign
                    if(code.selected)
                        this.selectedCodes.push(code.id)
                });
            }
        },
        migrate(){
            if (confirm(this.$i18n.t('Total to be migrated')+": "+this.totalSelected()+". "+this.$i18n.t('Are you sure?'))) {
                var params = {
                    'codes': this.selectedCodes,
                    'is_selected_all_codes': this.selectedAll,
                    'applied_filters':this.queries.group_id+'_'+this.queries.subgroup_id+'_'+this.queries.isZKS+'_'+this.queries.code
                    +'_'+this.queries.name+'_'+this.queries.description+'_'+this.queries.type,
                    //'migrate_group_id': this.migrate_group_id,
                    'migrate_subgroup_id': this.migrate_subgroup_id, 
                    'lang': this.$i18n.locale,
                }
                api.migrate('code',params)
                .then((response) => {
                    //console.log(response.data)
                    alert(this.$i18n.t('Successfully migrated')+": "+response.data.affected_rows)
                    this.$router.go()
                }).catch(e => {
                    this.basicErrorHandling(e)
                    if(e.response.status==422){
                      var message = this.$i18n.t(e.response.data.message) + '\n'
                      for(var key in e.response.data.errors){
                          e.response.data.errors[key].forEach((value)=>{
                              message += this.$i18n.t(value) +'\n'
                          })
                      }
                      alert(message)
                      console.log(message)
                    }
                })
            }
        },
        totalSelected(){
            if(this.selectedAll===true)
                return this.meta.total
            else
                return this.selectedCodes.length
        },
        getExportLink(){
            var params = this.fillParams()
            
            if(params['sort']==null && params['order']==null){
                params['sort']='id'
                params['order']='asc'
            }
            params['lang'] = this.$i18n.locale
            //params.responseType = 'blob'
            var url = new URL(window.location.origin+"/api/codes/export");
            const keys = Object.keys(params)
            for(const key of keys)
                url.searchParams.set(key, params[key]);
            url.searchParams.set('token',localStorage.getItem("dictionary_token"))
            return url
        },
        onFilterChanged(type,input,event=null){
            //if (event instanceof KeyboardEvent || event === null){
                this.filterChanged=true
                if(type==='group'){
                    this.fetchDatalist('','subgroup',input)
                    this.queries.subgroup_id=-1
                }else if(type==='migrate_group')
                    this.fetchDatalist('','migrate_subgroup',input)
            //}
        },
    }
}
</script>