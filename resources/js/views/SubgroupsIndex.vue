<template>
    <div class="container">
        <h2>
            {{$t('Subgroups')}}
            <router-link class="btn btn-outline-success float-right" :to="{ name: 'subgroups.create' }">{{$t('Add New')}}</router-link>
        </h2>
        <hr>
        <div class="row">
            <div class="col">
                <form class="form-inline">
                    <label class="sr-only" for="name">{{$t('Group')}}</label>
                    <select class="form-control mb-2 mr-sm-2" style="max-width:200px;" v-model="queries.group_id" @change="onFilterChanged('group',queries.group_id)">
                        <option value=-1>{{$t('Group')}}</option>
                        <option v-for="group in groups" :value="group.id">
                            {{display('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                        </option>
                    </select>
                    <!--<div class="input-group mb-2 mr-sm-2">
                        <input class="form-control" id="inlineFormInputName2" :placeholder="$t('Group')" :aria-label="$t('Group')"
                         list="groups" type="text" aria-describedby="basic-addon2"
                         v-model="queries.group_name" @change="onFilterChanged('group',queries.group_name)">
                        <datalist id="groups">
                          <option v-for="group in groups">{{display('name',group)}}</option>
                        </datalist>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" @click.prevent="queries.group_name=queries.name=subgroup_names=null;filterChanged=true">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>-->
                    <div class="input-group mb-2 mr-sm-2">
                        <input type=text list="code.name" class="form-control" id="nameInput" :placeholder="$t('Name')" :aria-label="$t('Name')"
                         v-model="queries.name" @change="filterChanged=true"
                         :disabled="queries.group_id==-1||!arrayIsSet(subgroup_names)">
                         <datalist id="code.name">
                          <option v-for="item in subgroup_names">{{display('name',item)}}</option>
                        </datalist>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" @click.prevent="queries.name=null;filterChanged=true">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <label class="sr-only" for="inlineFormInputName1">{{$t('ZKS')}}</label>
                    <select v-model="queries.isZKS" id=zks class="form-control mb-2 mr-sm-2" @change="filterChanged=true">
                        <option value selected>{{$t('ZKS')}} ({{$t('All')}})</option>
                        <option value="true">{{$t('Yes')}}</option>
                        <option value="false">{{$t('No')}}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary mb-2 mr-sm-2" @click.prevent="filter()"
                        :disabled="filterChanged===false">{{$t('Filter')}}</button>
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :class="{disabled: (selectedItems.length===0 || (selectedAll==true && filterApplied==false))}" 
                        data-toggle="modal" data-target="#migrationModal">
                        {{$t('Migrate')}}
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
        <div v-if="error" class="error">
            <p>{{ error }}</p>
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
                <thead v-if="selectedAll || selectedItems.length>0">
                    <tr>
                        <th colspan="10" class="alert-warning">
                            {{$t('Total selected')}}: {{totalSelected()}}
                        </th>
                    </tr>
                </thead>
                <thead class=""> 
                    <th scope="col">
                        #
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        {{$t('Group')}}
                        <i :class="getOrder('groups')"></i>
                    </th>
                    <th scope="col" :class="{'font-italic': (queries.sort==='name_kk'||queries.sort==='name_ru')}"
                        @click="sortBy('name')" style="cursor: pointer;">
                        {{$t('Name')}}
                        <i :class="getOrder('name')"></i>
                    </th>
                    <th scope="col" class="d-none d-sm-table-cell">{{$t('isZKS')}}</th>
                    <th scope="col">{{$t('Codes')}}</th>
                    <th scope="col">{{$t('Last modified by')}}</th>
                    <th scope="col">
                        <span class="float-right">
                            {{currentPage()}}/{{lastPage()}}
                        </span>
                    </th>
                </thead>
                <tbody v-if="subgroups!==null && subgroups.length>0">
                    <tr v-for="(subgroup,index) in subgroups" :class="{selected: subgroup.selected}" @click="select(subgroup)">
                        <th scope="row">
                            <i v-if="subgroup.selected" class="fa fa-check"></i>
                            {{ (currentPage()-1)*perPage()+index+1 }}
                        </th>
                        <td class="d-none d-md-table-cell name-cell">{{ display('name',subgroup.group) }}</td>
                        <td>{{ display('name',subgroup) }}</td>
                        <td class="d-none d-sm-table-cell"><i v-if="subgroup.group.isZKS" class="fa fa-check"></i></td>
                        <td>{{subgroup.codes_count}}</td>
                        <td>{{subgroup.user.name}}</td>
                        <td>
                            <div class="float-right" v-if="subgroup.name_kk!='Қалғандары'">
                                <router-link class="btn btn-outline-primary btn-sm" :to="getLink('edit',subgroup)">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button :disabled="saving" @click.prevent="onDelete(subgroup.id)" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="(subgroups!==null && subgroups.length===0)">
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
                            <select class="form-control mb-2 mr-sm-2" id="migrateGroup" v-model="migrate_group_id">
                                <option selected disabled value=-1>
                                    {{$t('Group')}}
                                </option>
                                <option v-for="group in migrate_groups" :value="group.id" :disabled="group.id===queries.group_id">
                                    {{display('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                                </option>
                            </select>
                        </form>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{$t('Cancel')}}</button>
                <button type="button" class="btn btn-primary" :disabled="!stringIsSet(migrate_group_id)" 
                @click.prevent="migrate()">{{$t('Migrate')}}</button>
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
    mixins: [common],
    data() {
        return {
            saving: false,
            pageCache: 1,
            perPageCache: 15,
            groups:null,
            subgroups:null,
            subgroup_names:null,
            meta: null,
            links: {
                first: null,
                last: null,
                next: null,
                prev: null,
            },
            queries:{
                sort: null,
                order: null,
                name: null,
                isZKS: '',
                group_id:-1,
            },
            error: null,
            selectedItems: [],
            selectedAll: false,
            selectedAllOnPage: false,
            filterChanged: false,
            filterApplied: false,
            migrate_group_id: null,
            migrate_groups: null,
        };
    },
    mounted(){
        this.fetchData()
        this.fetchDatalist('','group')
        this.fetchDatalist('','migrate_group')
    },
    watch:{
        '$route': 'fetchData'
    },
    methods: {
        fetchData(){
            this.filterApplied = false
            this.subgroups = this.links = this.meta = null
            this.setParams()
            this.getData(
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
                params.onlyWithSubgroups=true
            //else if(type==='migrate_group')
            //    params.onlyWithSubgroups=true
            this.request(type,params)
        },
        getData(params, callback){
            api.all('subgroup', {params} )
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
                        this.fetchDatalist('','subgroup_name',this.queries[key])
                }
            }
            this.filterApplied = filtered
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            if(this.queries.group_id>-1 
                || this.stringIsSet(this.queries.name))
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
                if((['group_id'].includes(key) && this.queries[key]>-1) ||
                (!['group_id'].includes(key) && this.queries[key]!=null && this.queries[key]!='' && Object.keys(this.queries).includes(key)))
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
                name: 'subgroups.index',
                query: this.fillParams(this.prevPage()),
            });
        },
        setData(err, data) {
            if (err) {
                this.basicErrorHandling(err)
            } else {
                this.subgroups = data.data;
                this.subgroups.forEach((subgroup, index) => {
                    subgroup.selected = false
                })
                this.links = data.links;
                this.meta = data.meta;
                this.selectedItems= []
                this.selectedAll= false
            }
        },
        goToCustomPage(page){
            if(page>this.meta.last_page)
                page = this.meta.last_page
            this.pageCache = this.meta.current_page;
            this.perPageCache = this.meta.per_page;
            this.$router.push({
                name: 'subgroups.index',
                query: this.fillParams(page)
            });
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
        sortBy(target){
            if(this.queries.sort!=null){
                if(this.queries.sort.startsWith(target)===false){
                    this.queries.order = 'asc';
                }else
                    this.queries.order = this.queries.order === 'asc' ? 'desc' : 'asc';
            }
            if(target==='groups'){
                this.queries.sort = target+'.name_'+this.$i18n.locale
            }else if(target==='name'){
                this.queries.sort = target+'_'+this.$i18n.locale
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
        getLink(which, target){
            if(which==='edit')
                return { 
                    name: 'subgroups.edit', 
                    params: {
                        'id': target.id 
                    }
                }

        },
        typeahead(input, type, except = null, parent = null, event = null){
            if (event instanceof KeyboardEvent || event === null){
                this.filterChanged = true
                var params = {} 
                  params.input = input
                  params.lang = this.$i18n.locale
                  params.except = except
                  params.parent = parent
                  const keys = Object.keys(this.queries)
                    if(type!='subgroup'&&type!='group')
                        for(const key of keys){
                            if(this.stringIsSet(this.queries[key]) && ['name'].includes(key))
                                params[key] = this.queries[key]
                        }
                    this.request(type,params)
            }
        },
        request(type,params){
            api.search(this.getType(type), params).then((response) => {
                this[type+'s'] = response.data
            }).catch(e => {
                this.basicErrorHandling(e)
            });
        },
        filter(){
            this.goToCustomPage(1)
        },
        select(target){
            target.selected = !target.selected
            if(target.selected)
                this.selectedItems.push(target.id)
            else{
                this.selectedAll = this.selectedAllOnPage = false
                var index = this.selectedItems.indexOf(target.id);
                if (index > -1)
                  this.selectedItems.splice(index, 1);
            }
        },
        selectAll(type, event){
            if(this.filterApplied==false && type=="all"){
                alert(this.$i18n.t('You have to apply some filters before selecting all items'))
            }else{ 
                var assign = false;
                if(type=='all'){
                    assign = !this.selectedAll
                    this.selectedAllOnPage = assign
                    
                }else if(type=="page"){
                    assign = !this.selectedAllOnPage
                    this.selectedAll = false
                }
                this.selectedItems = [];
                this.subgroups.forEach((code, index) => {
                    code.selected = assign
                    if(code.selected)
                        this.selectedItems.push(code.id)
                });
            }
        },
        migrate(){
            if (confirm(this.$i18n.t('Total to be migrated')+": "+this.totalSelected()+". "+this.$i18n.t('Are you sure?'))) {
                var params = {
                    'items': this.selectedItems,
                    'is_selected_all': this.selectedAll,
                    'applied_filters':this.queries.group_id+'_'+this.queries.isZKS
                    +'_'+this.queries.name,
                    'migrate_group_id': this.migrate_group_id,
                    'lang': this.$i18n.locale,
                }
                api.migrate('subgroup',params)
                .then((response) => {
                    //console.log(response.data)
                    alert(this.$i18n.t('Successfully migrated')+": "+response.data.affected_rows)
                    this.$router.go()
                }).catch(e => {
                    this.basicErrorHandling(e)
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
        totalSelected(){
            if(this.selectedAll===true)
                return this.meta.total
            else
                return this.selectedItems.length
        },
        onFilterChanged(type,input){
            this.filterChanged=true
            if(type==='group')
                this.fetchDatalist('','subgroup_name',input)
            //else if(type==='migrate_group')
            //        this.fetchDatalist('','migrate_subgroup',input)

        },

        onDelete(id) {
            if (confirm(this.$i18n.t('Are you sure that you want to delete that ')+"?")) {
                this.saving = true;
                api.delete('subgroup',id)
               .then((response) => {
                    alert(this.$i18n.t('Subgroup Deleted')+', '+this.$i18n.t('codes migrated')+": "+response.data.migrated_childs);
                  this.$router.go()
               }).catch(e => {
                this.basicErrorHandling(e)
               });
            }
        },
    }
}
</script>