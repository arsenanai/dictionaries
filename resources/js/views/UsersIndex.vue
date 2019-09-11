<template>
    <div class="container">
        <h2>
            {{$t('Users')}}
            <router-link class="btn btn-outline-success float-right" :to="{ name: 'users.create' }">{{$t('Add New')}}</router-link>
        </h2>
        <hr>
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
                    <th scope="col">
                        #
                    </th>
                    <th scope="col">
                        {{$t('Name')}}
                    </th>
                    <th scope="col">
                        {{$t('Email')}}
                    </th>
                    <th scope="col">
                        <span class="float-right">
                            {{currentPage()}}/{{lastPage()}}
                        </span>
                    </th>
                </thead>
                <tbody v-if="users!==null && users.length>0">
                    <tr v-for="(user,index) in users">
                        <th scope="row">{{ (currentPage()-1)*perPage()+index+1 }}</th>
                        <td>{{user.name}}</td>
                        <td>{{user.email}}</td>
                        <td>
                            <div class="float-right">
                                <router-link class="btn btn-outline-primary btn-sm" :to="getLink('edit',user)">
                                    <i class="fa fa-edit"></i>
                                </router-link>
                                <!--<button :disabled="saving" @click.prevent="onDelete(user.id)" class="btn btn-outline-danger btn-sm">
                                    <i class="fa fa-trash"></i>
                                </button>-->
                            </div>
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
            users:null,
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
                email:null,
            },
            error: null,
            filterChanged: false,
            filterApplied: false,
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
            this.users = this.links = this.meta = null
            this.setParams()
            this.getData(
                this.$route.query,
                (err, data) => {
                    this.setData(err, data);
                //next();
            });
        },
        getData(params, callback){
            api.all('user', {params} )
                .then(response => {
                    callback(null, response.data);
                }).catch(error => {
                    callback(error, error.response);
                });
        },
        setParams(){
            for(var key in this.$route.query) 
                this.queries[key]=this.$route.query[key];
        },
        fillParams(page){
            var params = {} 
            if(page>1)
                params.page = page
            if(this.queries.user_name || this.queries.name)
                params.lang = this.$i18n.locale
            const keys = Object.keys(this.queries)
            for(const key of keys){
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
                name: 'users.index',
                query: this.fillParams(this.prevPage()),
            });
        },
        setData(err, data) {
            if (err) {
                basicErrorHandling(err)
            } else {
                this.users = data.data;
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
                name: 'users.index',
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
        getLink(which, target){
            if(which==='edit')
                return { 
                    name: 'users.edit', 
                    params: {
                        'id': target.id 
                    }
                }

        },
        getType(type){
            return type
        },
    }
}
</script>