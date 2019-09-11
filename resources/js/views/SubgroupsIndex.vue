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
                    <label class="sr-only" for="inlineFormInputName2">{{$t('Group')}}</label>
                    <input class="form-control mb-2 mr-sm-2" id="inlineFormInputName2" :placeholder="$t('Group')" 
                     list="groups" @keyup="typeahead($event.target.value, 'group')"
                     v-model="queries.group_name" @change="filterChanged=true">
                    <datalist id="groups">
                      <option v-for="group in groups" :value="display('name',group)"></option>
                    </datalist>
                    <label class="sr-only" for="nameInput">{{$t('Name')}}</label>
                    <input class="form-control mb-2 mr-sm-2" id="nameInput" :placeholder="$t('Name')" 
                     v-model="queries.name" @change="filterChanged=true" 
                     @keyup="typeahead($event.target.value, 'subgroup')" list="names">
                     <datalist id="names">
                      <option v-for="item in types" :value="display('name',item)"></option>
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
                    <a class="btn btn-outline-secondary mb-2 mr-sm-2" :class="{disabled: (selectedItems.length===0)}"  
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
            <table class="table table-sm table-bordered table-striped table-hover" >
                <thead class=""> 
                    <th scope="col" @click="selectAll()">#
                    </th>
                    <th scope="col" class="d-none d-md-table-cell">
                        {{$t('Group')}}
                        <i :class="getOrder('groups')"></i>
                    </th>
                    <th scope="col" :class="{'font-italic': (queries.sort==='name_kk'||queries.sort==='name_ru')}"
                        @click="sortBy('subgroups.name')">
                        {{$t('Name')}}
                        <i :class="getOrder('name')"></i>
                    </th>
                    <th scope="col" class="d-none d-sm-table-cell">{{$t('isZKS')}}</th>
                    <th scope="col">
                        <span class="float-right">
                            {{currentPage()}}/{{lastPage()}}
                        </span>
                    </th>
                </thead>
                <tbody v-if="subgroups!==null && subgroups.length>0">
                    <tr v-for="(subgroup,index) in subgroups" :class="{selected: subgroup.selected}">
                        <th scope="row" @click="select(subgroup)">{{ (currentPage()-1)*perPage()+index+1 }}</th>
                        <td class="d-none d-md-table-cell name-cell">{{ display('name',subgroup.group) }}</td>
                        <td>{{ display('name',subgroup) }}</td>
                        <td class="d-none d-sm-table-cell"><i v-if="subgroup.group.isZKS" class="fas fa-check"></i></td>
                        <td>
                            <div class="float-right">
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
        <span class="alert alert-warning" v-if="selectedAll || selectedItems.length>0">
            {{$t('Total selected')}}: {{totalSelected()}}
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
                        <form>
                            <label class="sr-only" for="migrateGroup">{{$t('Group')}}</label>
                            <input class="form-control mb-2 mr-sm-2" id="migrateGroup" :placeholder="$t('Group')" 
                             list="migrate_groups" @keyup="typeahead($event.target.value, 'migrate_group',queries.group_name)" v-model="migrate_group_name">
                            <datalist id="migrate_groups">
                              <option v-for="group in migrate_groups" :value="display('name',group)"></option>
                            </datalist>
                        </form>
                    </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{$t('Cancel')}}</button>
                <button type="button" class="btn btn-primary" :disabled="migrate_group_name==null" 
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
            types: null,
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
                group_name:null,
            },
            error: null,
            selectedItems: [],
            selectedAll: false,
            filterChanged: false,
            filterApplied: false,
            migrate_group_name: null,
            migrate_groups: null,
        };
    },
    mounted(){
        this.fetchData()
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
        getData(params, callback){
            api.all('subgroup', {params} )
                .then(response => {
                    callback(null, response.data);
                }).catch(error => {
                    callback(error, error.response);
                });
        },
        setParams(){
            for(var key in this.$route.query) 
                    if(['sort','order','isZKS','name'].includes(key))
                        this.queries[key]=this.$route.query[key];
                if(
                     this.queries.isZKS !== ''  
                    || this.queries.name !== ''
                    || this.queries.group_name!==null 
                ){
                    this.filterApplied = true
                }
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            if(this.queries.group_name || this.queries.name)
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
                if(this.queries[key]!=null && this.queries[key]!='' &&['sort','order','group_name','isZKS','name'].includes(key))
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
                basicErrorHandling(err)
            } else {
                this.subgroups = data.data;
                this.subgroups.forEach((subgroup, index) => {
                    subgroup.selected = false
                })
                this.links = data.links;
                this.meta = data.meta;
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
                    return (this.queries.order==='desc') ? 'fas fa-caret-up' : 'fas fa-caret-down'
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
        typeahead(input, type, except = null, parent = null){
            if(input.length >1){
                var params = {} 
                  params.input = input
                  params.lang = this.$i18n.locale
                  params.except = except
                  params.parent = parent
                api.search(this.getType(type), params).then((response) => {
                    if(type==='subgroup')
                        this['types'] = response.data.data
                    else
                        this[type+'s'] = response.data.data
                }).catch(e => {
                    basicErrorHandling(e)
                });
            }
        },
        getType(type){
            if(type.startsWith('migrate_'))
                return type.split('_')[1]
            else
                return type
        },
        filter(){
            this.goToCustomPage(1)
        },
        select(target){
            target.selected = !target.selected
            if(target.selected)
                this.selectedItems.push(target.id)
            else{
                var index = this.selectedItems.indexOf(target.id);
                if (index > -1)
                  this.selectedItems.splice(index, 1);
            }
        },
        selectAll(){
            if(this.filterApplied==false){
                alert(this.$i18n.t('You have to apply some filters before selecting all'))
            }else{
                var assign = !this.selectedAll
                this.selectedAll = assign
                this.selectedItems = [];
                this.subgroups.forEach((target, index) => {
                    target.selected = assign
                    if(this.selectedAll==true)
                        this.selectedItems.push(target.id)
                });
            }
        },
        migrate(){
            if (confirm(this.$i18n.t('Total to be migrated: ')+this.totalSelected()+". "+this.$i18n.t('Are you sure?'))) {
                var params = {
                    'items': this.selectedItems,
                    'is_selected_all': this.selectedAll,
                    'applied_filters':this.queries.group_name+'_'+this.queries.isZKS
                    +'_'+this.queries.name,
                    'migrate_group_name': this.migrate_group_name,
                    'lang': this.$i18n.locale,
                }
                api.migrate('subgroup',params)
                .then((response) => {
                    //console.log(response.data)
                    alert(this.$i18n.t('Successfully migrated: ')+response.data.affected_rows)
                    this.$router.go()
                }).catch(e => {
                    basicErrorHandling(e)
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
        onDelete(id) {
            if (confirm(this.$i18n.t('Are you sure that you want to delete that ')+"?")) {
                this.saving = true;
                api.delete('subgroup',id)
               .then((response) => {
                    console.log(response)
                  this.message = 'Subgroup Deleted';
                  var toDelete = -1;
                  this.subgroups.every((code, i) => {
                    if(code.id===id){
                        toDelete = i
                        return false
                    }else
                        return true
                  });
                  if(toDelete!=-1)
                    this.codes.splice(toDelete, 1);
               }).catch(e => {
                basicErrorHandling(e)
               });
            }
        },
    }
}
</script>