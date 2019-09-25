<template>
    <div class="container">
        <h2>
            {{$t('Groups')}}
            <router-link class="btn btn-outline-success float-right" :to="{ name: 'groups.create' }">{{$t('Add New')}}</router-link>
        </h2>
        <hr>
        <div class="row">
            <div class="col">
                <form class="form-inline">
                    <label class="sr-only" for="name">{{$t('Name')}}</label>
                    <select class="form-control mb-2 mr-sm-2" style="max-width:200px;" v-model="queries.id" @change="filterChanged=true">
                        <option value=-1>{{$t('Name')}}</option>
                        <option v-for="group in group_names" :value="group.id">
                            {{display('name',group)+((group.isZKS==true) ? " ("+$t('ZKS')+")" : '')}}
                        </option>
                    </select>
                    <label class="sr-only" for="zks">{{$t('ZKS')}}</label>
                    <select v-model="queries.isZKS" id=zks class="form-control mb-2 mr-sm-2" @change="filterChanged=true">
                        <option value selected>{{$t('ZKS')}} ({{$t('All')}})</option>
                        <option value="true">{{$t('Yes')}}</option>
                        <option value="false">{{$t('No')}}</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary mb-2 mr-sm-2" @click.prevent="filter()"
                        :disabled="filterChanged===false">{{$t('Filter')}}</button>
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
                    <th scope="col">#
                    </th>
                    <th scope="col" :class="{'font-italic': (queries.sort==='name_kk'||queries.sort==='name_ru')}"
                        @click="sortBy('name')" style="cursor: pointer;">
                        {{$t('Name')}}
                        <i :class="getOrder('name')"></i>
                    </th>
                    <th scope="col" class="d-none d-sm-table-cell">{{$t('isZKS')}}</th>
                    <th scope="col">{{$t('Subgroups')}}</th>
                    <th scope="col">
                        <span class="float-right">
                            {{currentPage()}}/{{lastPage()}}
                        </span>
                    </th>
                </thead>
                <tbody v-if="groups!==null && groups.length>0">
                    <tr v-for="(group,index) in groups">
                        <th scope="row">
                            {{ (currentPage()-1)*perPage()+index+1 }}
                        </th>
                        <td>{{ display('name',group) }}</td>
                        <td class="d-none d-sm-table-cell"><i v-if="group.isZKS" class="fa fa-check"></i></td>
                        <td>{{group.subgroups_count}}</td>
                        <td>
                            <div class="float-right" v-if="group.name_kk!='Қалғандары'">
                                <router-link class="btn btn-outline-primary btn-sm" :to="getLink('edit',group)" >
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <button :disabled="saving" @click.prevent="onDelete(group.id)" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="(groups!==null && groups.length===0)">
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
            group_names: null,
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
                id: -1,
                isZKS: '',
            },
            error: null,
            filterChanged: false,
            filterApplied: false,
        };
    },
    mounted(){
        this.fetchData()
        this.typeahead('','group_name')
    },
    watch:{
        '$route': 'fetchData'
    },
    methods: {
        fetchData(){
            this.filterApplied = false
            this.groups = this.links = this.meta = null
            this.setParams()
            this.getData(
                this.$route.query,
                (err, data) => {
                    this.setData(err, data);
                //next();
            });
        },
        getData(params, callback){
            api.all('group',{params})
                .then(response => {
                    callback(null, response.data);
                }).catch(error => {
                    callback(error, error.response);
                });
        },
        setParams(){
            for(var key in this.$route.query) 
                if(['sort','order','isZKS','id'].includes(key))
                    this.queries[key]=this.$route.query[key];
                if(
                     this.queries.isZKS !== ''  
                    || this.queries.id !== -1
                    || this.queries.group_name!==null 
                ){
                    this.filterApplied = true
                }
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            if(this.queries.group_name || this.queries.id)
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
                if(this.queries[key]!=null && this.queries[key]!=-1 && this.queries[key]!='' &&['sort','order','group_name','isZKS','id'].includes(key))
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
                name: 'groups.index',
                query: this.fillParams(this.prevPage()),
            });
        },
        setData(err, data) {
            if (err) {
                basicErrorHandling(err)
            } else {
                this.groups = data.data;
                this.groups.forEach((group, index) => {
                    group.selected = false
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
                name: 'groups.index',
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
                if(this.queries.sort.startsWith(target))
                    return (this.queries.order==='desc') ? 'fa fa-caret-up' : 'fa fa-caret-down'
                else
                    return ''
        },
        getLink(which, target){
            if(which==='edit')
                return { 
                    name: 'groups.edit', 
                    params: {
                        'id': target.id 
                    }
                }

        },
        typeahead(input, type, except = null, parent = null, event = null){
            if (event instanceof KeyboardEvent || event === null){
                var params = {} 
                  params.input = input
                  params.lang = this.$i18n.locale
                  params.except = except
                  params.parent = parent
                  const keys = Object.keys(this.queries)
                    if(type!='subgroup'&&type!='group')
                        for(const key of keys){
                            if(this.queries[key]!=null && this.queries[key]!='' &&['code','id','description'].includes(key))
                                params[key] = this.queries[key]
                        }
                    api.search(this.getType(type), params).then((response) => {
                        this[type+'s'] = response.data
                    }).catch(e => {
                        this.basicErrorHandling(e)
                    });
            }
        },
        filter(){
            this.goToCustomPage(1)
        },
        onDelete(id) {
            if (confirm(this.$i18n.t('Are you sure that you want to delete that ')+"?")) {
                this.saving = true;
                api.delete('group',id)
               .then((response) => {
                    alert(this.$i18n.t('Group Deleted')+', '+this.$i18n.t('subgroups migrated: ')+" "+response.data.migrated_childs);
                  this.$router.go()
               }).catch(e => {
                this.basicErrorHandling(e)
               });
            }
        },

    }
}
</script>