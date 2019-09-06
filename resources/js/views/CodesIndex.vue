<template>
    <div class="container">
        <h2>
            {{$t('Codes')}}
            <router-link class="btn btn-outline-success float-right" :to="{ name: 'codes.create' }">{{$t('Add New')}}</router-link>
        </h2>
        <hr>
        <div class="row">
            <div class="col">
                <form class="form-inline">
                    <label class="sr-only" for="inlineFormInputName2">{{$t('Group')}}</label>
                    <input class="form-control mb-2 mr-sm-2" id="inlineFormInputName2" :placeholder="$t('Group')" 
                     list="groups" @keyup="typeahead($event.target.value, 'group')"
                     v-model="queries.group_name" @change="filterChanged=true">
                    <datalist id="groups">
                      <option v-for="group in groups" :value="display('name',group)"></option>
                    </datalist>
                    <label class="sr-only" for="inlineFormInputName3">{{$t('Subgroup')}}</label>
                    <input list="subgroups" class="form-control mb-2 mr-sm-2" id="inlineFormInputName3" :placeholder="$t('Subgroup')"
                    @keyup="typeahead($event.target.value, 'subgroup', null, queries.group_name)" v-model="queries.subgroup_name" @change="filterChanged=true"
                    >
                    <datalist id="subgroups">
                      <option v-for="subgroup in subgroups" :value="display('name',subgroup)"></option>
                    </datalist>
                    <label class="sr-only" for="codeInput">{{$t('Code')}}</label>
                    <input list="code.code" class="form-control mb-2 mr-sm-2" id="codeInput" :placeholder="$t('Code')" 
                     v-model="queries.code" @change="filterChanged=true" @keyup="typeahead($event.target.value, 'code')">
                     <datalist id="code.code">
                      <option v-for="item in types" :value="item.code"></option>
                    </datalist>
                    <label class="sr-only" for="nameInput">{{$t('Name')}}</label>
                    <input list="code.name" class="form-control mb-2 mr-sm-2" id="nameInput" :placeholder="$t('Name')" 
                     v-model="queries.name" @change="filterChanged=true" @keyup="typeahead($event.target.value, 'code')">
                     <datalist id="code.name">
                      <option v-for="item in types" :value="display('name',item)"></option>
                    </datalist>
                    <label class="sr-only" for="descriptionInput">{{$t('Description')}}</label>
                    <input list="code.desc" class="form-control mb-2 mr-sm-2" id="descriptionInput" :placeholder="$t('Description')" 
                     v-model="queries.description" @change="filterChanged=true" @keyup="typeahead($event.target.value, 'code')">
                     <datalist id="code.desc">
                      <option v-for="item in types" :value="display('description',item)"></option>
                    </datalist>
                     <label class="sr-only" for="inlineFormInputName1">{{$t('ZKS')}}</label>
                    <select v-model="queries.isZKS" class="form-control mb-2 mr-sm-2" @change="filterChanged=true">
                        <option value selected disabled>{{$t('isZKS')}}</option>
                        <option value >{{$t('All')}}</option>
                        <option value="true">{{$t('Yes')}}</option>
                        <option value="false">{{$t('No')}}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary mb-2 mr-sm-2" @click.prevent="filter()"
                        :disabled="filterChanged===false">{{$t('Filter')}}</button>
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :class="{disabled: (selectedCodes.length===0)}"  
                        data-toggle="modal" data-target="#migrationModal">
                        {{$t('Migrate')}}
                    </a>
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :href="getExportLink()" target="_blank">
                        CSV
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
            <table class="table table-sm table-bordered table-striped table-hover" >
                <thead class=""> 
                    <th scope="col" @click="selectAll()">#
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        {{$t('Group')}}
                        <i :class="getOrder('groups')"></i>
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        {{$t('Subgroup')}}
                        <i :class="getOrder('subgroups')"></i>
                    </th>
                    <th scope="col" :class="{'font-italic': queries.sort==='code'}"
                        @click="sortBy('code')">
                        {{$t('Code')}}
                        <i :class="getOrder('code')"></i>
                    </th>
                    <th scope="col" :class="{'font-italic': (queries.sort==='name_kk'||queries.sort==='name_ru')}"
                        @click="sortBy('codes.name')">
                        {{$t('Name')}}
                        <i :class="getOrder('name')"></i>
                    </th>
                    <th scope="col" class="d-none d-sm-table-cell" :class="{'font-italic': (queries.sort==='description_kk'||queries.sort==='description_ru')}"
                        @click="sortBy('description')">
                        {{$t('Description')}}
                        <i :class="getOrder('description')"></i>
                    </th>
                    <th scope="col" class="d-none d-sm-table-cell">{{$t('isZKS')}}</th>
                    <th scope="col">
                        <span class="float-right">
                            {{currentPage()}}/{{lastPage()}}
                        </span>
                    </th>
                </thead>
                <tbody v-if="codes!==null && codes.length>0">
                    <tr v-for="(code,index) in codes" :class="{selected: code.selected}">
                        <th scope="row" @click="select(code)">{{ (currentPage()-1)*perPage()+index+1 }}</th>
                        <td class="d-none d-md-table-cell name-cell">{{display('name',code.group)}}</td>
                        <td class="d-none d-md-table-cell name-cell">{{display('name',code.subgroup)}}</td>
                        <td>{{ code.code }}</td>
                        <td>{{ display('name',code) }}</td>
                        <td class="d-none d-sm-table-cell">{{display('description',code)}}</td>
                        <td class="d-none d-sm-table-cell"><i v-if="code.isZKS" class="fas fa-check"></i></td>
                        <td>
                            <div class="float-right">
                                <router-link class="btn btn-outline-primary btn-sm" :to="getLink('edit',code)">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button :disabled="saving" @click.prevent="onDelete(code.id)" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="(codes!==null && codes.length===0)">
                    <tr>
                        <td class="font-italic" colspan=8>
                            {{$t('Search returned an empty result')}}
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                       <td colspan=8>
                           {{$t('Loading')}} ...
                       </td> 
                    </tr>
                </tbody>
            </table>
        </div>
        <span class="alert alert-warning" v-if="selectedAll || selectedCodes.length>0">
            {{$t('Total selected')}}: {{totalSelectedCodes()}}
        </span>
        <div class="modal fade" id="migrationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
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
                        <form class="form-inline">
                            <label class="sr-only" for="migrateGroup">{{$t('Group')}}</label>
                            <input class="form-control mb-2 mr-sm-2" id="migrateGroup" :placeholder="$t('Group')" 
                             list="migrate_groups" @keyup="typeahead($event.target.value, 'migrate_group',queries.group_name)" v-model="migrate_group_name">
                            <datalist id="migrate_groups">
                              <option v-for="group in migrate_groups" :value="display('name',group)"></option>
                            </datalist>
                            <label class="sr-only" for="migrateSubgroup">{{$t('Subgroup')}}</label>
                            <input list="migrate_subgroups" class="form-control mb-2 mr-sm-2" id="migrateSubgroup" :placeholder="$t('Subgroup')"
                            @keyup="typeahead($event.target.value, 'migrate_subgroup',queries.subgroup_name,migrate_group_name)"
                             v-model="migrate_subgroup_name">
                            <datalist id="migrate_subgroups">
                              <option v-for="subgroup in migrate_subgroups" :value="display('name',subgroup)"></option>
                            </datalist>
                        </form>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{$t('Cancel')}}</button>
                <button type="button" class="btn btn-primary" :disabled="migrate_group_name==null && migrate_subgroup_name==null" @click.prevent="migrate()">{{$t('Migrate')}}</button>
              </div>
            </div>
          </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import api from '../api/codes';
import {common} from '../common.js'
const getCodes = (params, callback) => {
    axios.defaults.headers.common['Authorization'] = 'Bearer '+localStorage.getItem("enstru_token");
    axios
        .get('/api/codes', {params} )
        .then(response => {
            callback(null, response.data);
        }).catch(error => {
            callback(error, error.response);
        });
};

export default {
    mixins:[common],
    data() {
        return {
            saving: false,
            codes: null,
            types:null,
            pageCache: 1,
            perPageCache: 15,
            meta: null,
            queries:{
                sort: null,
                order: null,
                group_name: null,
                subgroup_name: null,
                code: null,
                name: null,
                description: null,
                isZKS: '',
            },
            groups: null,
            subgroups: null,
            migrate_group_name: null,
            migrate_subgroup_name: null,
            migrate_groups: null,
            migrate_subgroups: null,
            filterChanged: false,
            filterApplied: false,
            selectedCodes: [],
            selectedAll: false,
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
        axios.defaults.headers.common['Accept-Language'] = this.$i18n.locale
    },
    watch:{
        '$route': 'fetchData'
    },
    methods: {
        fetchData(){
            this.filterApplied = false
            this.codes = this.links = this.meta = null
            this.setParams()
            getCodes(
                this.$route.query,
                (err, data) => {
                    this.setData(err, data);
                //next();
            });
        },
        setParams(){
            for(var key in this.$route.query) 
                if(['sort','order','group_name','subgroup_name','isZKS','code','name','description'].includes(key))
                    this.queries[key]=this.$route.query[key];
            if(this.queries.subgroup_name!==null || this.queries.group_name!==null 
                || this.queries.isZKS !== '' || this.queries.code !== '' || this.queries.name !== '' || this.queries.description !== ''){
                this.filterApplied = true
            }
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            //if(this.queries.group_name || this.queries.subgroup_name || this.queries.name || this.queries.description)
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
                if(this.queries[key]!=null && this.queries[key]!='' &&['sort','order','group_name','subgroup_name','isZKS','code','name','description'].includes(key))
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
        setData(err, data) {
            if (err) {
                this.error = err.toString();
                if(this.error.includes('401'))
                    this.redirectToLogin()
            } else {
                this.codes = data.data;
                this.codes.forEach((code, index) => {
                    code.selected = false
                })
                this.links = data.links;
                this.meta = data.meta;
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
        typeahead(input, type, except = null, parent = null){
            if(input.length >1){
                var params = {} 
                  params.input = input
                  params.lang = this.$i18n.locale
                  params.except = except
                  params.parent = parent
                  const keys = Object.keys(this.queries)
                    for(const key of keys){
                        if(this.queries[key]!=null && this.queries[key]!='' &&['code','name','description'].includes(key))
                            params[key] = this.queries[key]
                    }
                api.search(this.getType(type), params).then((response) => {
                    if(type==='code')
                        this['types'] = response.data.data
                    else
                        this[type+'s'] = response.data.data
                }).catch(e => {
                    if(e.response.status==401)
                        this.redirectToLogin()
                });
            }
        },
        getType(type){
            if(type.startsWith('migrate_'))
                return type.split('_')[1]
            else
                return type
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
                    return (this.queries.order==='desc') ? 'fas fa-caret-up' : 'fas fa-caret-down'
                }else
                    return ''
        },
        onDelete(id) {
            if (confirm(this.$i18n.t('Are you sure that you want to delete that ')+this.$i18n.t('Code')+"?")) {
                this.saving = true;
                api.delete(id)
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
                if(e.response.status==401)
                  this.redirectToLogin()
               });
            }
        },
        select(code){
            code.selected = !code.selected
            if(code.selected)
                this.selectedCodes.push(code.id)
            else{
                var index = this.selectedCodes.indexOf(code.id);
                if (index > -1)
                  this.selectedCodes.splice(index, 1);
            }
        },
        selectAll(){
            if(this.filterApplied==false){
                alert(this.$i18n.t('You have to apply some filters before selecting all items'))
            }else{
                var assign = !this.selectedAll
                this.selectedAll = assign
                this.selectedCodes = [];
                this.codes.forEach((code, index) => {
                    code.selected = assign
                    if(this.selectedAll==true)
                        this.selectedCodes.push(code.id)
                });
            }
        },
        migrate(){
            if (confirm(this.$i18n.t('Total to be migrated: ')+this.totalSelectedCodes()+". "+this.$i18n.t('Are you sure?'))) {
                var params = {
                    'codes': this.selectedCodes,
                    'is_selected_all_codes': this.selectedAll,
                    'applied_filters':this.queries.group_name+'_'+this.queries.subgroup_name+'_'+this.queries.isZKS+'_'+this.queries.code
                    +'_'+this.queries.name+'_'+this.queries.description,
                    'migrate_group_name': this.migrate_group_name,
                    'migrate_subgroup_name': this.migrate_subgroup_name, 
                    'lang': this.$i18n.locale,
                }
                api.migrate(params)
                .then((response) => {
                    //console.log(response.data)
                    alert(this.$i18n.t('Successfully migrated: ')+response.data.affected_rows)
                    this.$router.go()
                }).catch(e => {
                    console.log(e)
                    if(e.response.status==401)
                      this.redirectToLogin()
                    if(e.response.status==422){
                        var message = e.response.data.message + '\n'
                        for(var key in e.response.data.errors){
                            e.response.data.errors[key].forEach((value)=>{
                                message += value +'\n'
                            })
                        }
                        alert(message)
                    }
                })
            }
        },
        totalSelectedCodes(){
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
            //params.responseType = 'blob'
            var url = new URL(window.location.origin+"/api/codes/export");
            const keys = Object.keys(params)
            for(const key of keys)
                url.searchParams.set(key, params[key]);
            
            url.searchParams.set('token',localStorage.getItem("enstru_token"))
            return url
            /*api.excel(params)
                .then((response) => {
                    download(
                        response.data,
                        'codes.xlsx',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                      )
                }).catch((error) => {
                    console.log(error)
                })*/
        },
    }
}
</script>