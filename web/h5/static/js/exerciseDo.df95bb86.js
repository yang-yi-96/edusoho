(window.webpackJsonp=window.webpackJsonp||[]).push([["exerciseDo"],{"057e":function(e,t,i){"use strict";var s=i("db72"),n=i("2f62"),a={name:"GuidePage",data:function(){return{isFristVisited:!1}},computed:Object(s.a)({},Object(n.d)({user:function(e){return e.user}})),created:function(){this.setVisited()},methods:{setVisited:function(){var e="".concat(this.user.id,"-task-visited");localStorage.getItem(e),localStorage.getItem(e)||(this.isFristVisited=!0,localStorage.setItem(e,!0))}}},r=i("0c7c"),c=Object(r.a)(a,(function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{directives:[{name:"show",rawName:"v-show",value:e.isFristVisited,expression:"isFristVisited"}],staticClass:"guide",on:{click:function(t){e.isFristVisited=!1}}},[i("div",{staticClass:"guide__text"},[e._v("左右切换滑动")]),e._m(0)])}),[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"guide__gesture"},[t("img",{attrs:{src:"static/images/leftslide.png"}}),t("img",{attrs:{src:"static/images/rightslide.png"}})])}],!1,null,null,null);t.a=c.exports},"391c":function(e,t,i){"use strict";i("e17f");var s=i("2241");i("456d"),i("ac6a"),t.a={methods:{canDoing:function(e,t){return new Promise((function(i,n){if(e&&"doing"===e.status){var a="exercise-".concat(t,"-").concat(e.id),r=JSON.parse(localStorage.getItem(a));r=r?Object.keys(r).forEach((function(e){r[e]=r[e].filter((function(e){return""!==e}))})):{},s.a.confirm({title:"提示",cancelButtonText:"放弃做题",confirmButtonText:"继续做题",message:"您有未完成的练习，是否继续？"}).then((function(){i()})).catch((function(){n({answer:r})}))}}))}}}},ea75:function(e,t,i){"use strict";i.r(t),i("a481"),i("456d"),i("c5f6"),i("ac6a"),i("e7e5");var s=i("d399"),n=(i("e17f"),i("2241")),a=i("db72"),r=i("bd86"),c=i("3ce7"),o=i("faa5"),u=i("2f62"),l=i("057e"),d=i("f5cc"),f=i("391c"),h=i("8ae7"),m="",g={name:"ExerciseDo",components:{itemBank:d.a,guidePage:l.a},mixins:[f.a,h.a],data:function(){var e;return e={info:[],answer:{},lastAnswer:null,cardSeq:0,exercise:null,cardShow:!1,localanswerName:null,localtimeName:null,lastUsedTime:null},Object(r.a)(e,"lastAnswer",null),Object(r.a)(e,"usedTime",null),Object(r.a)(e,"isHandExercise",!1),Object(r.a)(e,"slideIndex",0),e},computed:Object(a.a)({},Object(u.d)({isLoading:function(e){return e.isLoading},user:function(e){return e.user}})),watch:{answer:{handler:"saveAnswer",deep:!0}},created:function(){this.getData()},beforeRouteEnter:function(e,t,i){m="/"===t.fullPath?"/":"",i()},beforeRouteLeave:function(e,t,i){0==this.info.length||this.isHandExercise||"doing"!=this.exercise.status?i():this.submitpaper()?i():i(!1)},beforeDestroy:function(){this.clearTime(),n.a.close()},methods:Object(a.a)({},Object(u.c)({setNavbarTitle:o.k}),{},Object(u.b)("course",["handExercisedo"]),{getData:function(){var e=this,t=this.$route.query.exerciseId,i=this.$route.query.targetId;this.$route.query.action,c.a.getExerciseInfo({query:{exerciseId:t},data:{targetId:i,targetType:"task"}}).then((function(t){e.afterGetData(t)})).catch((function(t){var i=s.a.fail(t.message);"4036706"==t.code&&setTimeout((function(){e.toIntro(),i.clear()}),2e3)}))},afterGetData:function(e){this.setNavbarTitle(e.paperName),this.exercise=e,this.isDoing()||(this.getLocalData(),this.formatData(e),this.interruption(),this.saveTime())},isDoing:function(){return"doing"!=this.exercise.status&&(this.showResult(),!0)},interruption:function(){var e=this;this.$route.params.KeepDoing||this.canDoing(this.exercise,this.user.id).then((function(){})).catch((function(t){var i=t.answer;e.submitExercise(i)}))},formatData:function(e){var t=this;e.items.forEach((function(e){if("material"!=e.type){var i=t.sixType(e.type,e,t.lastAnswer);t.$set(t.answer,e.id,i.answer),t.info.push(i.item)}if("material"==e.type){var s=Object.assign({},e,{subs:""});e.subs.forEach((function(i,n){i.parentTitle=s,i.parentType=e.type,i.materialIndex=n+1;var a=t.sixType(i.type,i,t.lastAnswer);t.$set(t.answer,i.id,a.answer),t.info.push(a.item)}))}}))},formatStatus:function(e,t){var i=0;return("single_choice"==e||"choice"==e||"uncertain_choice"==e||"determine"==e)&&this.answer[t].length>0?i=1:"essay"==e&&""!=this.answer[t][0]?i=1:"fill"==e?i=Number(this.answer[t].some((function(e){return""!=e}))):i},slideToNumber:function(e){var t=Number(e);this.cardSeq=t,this.cardShow=!1},getLocalData:function(){this.localanswerName="exercise-".concat(this.user.id,"-").concat(this.exercise.id),this.localuseTime="exercise-".concat(this.user.id,"-").concat(this.exercise.id,"-usedTime"),this.lastAnswer=JSON.parse(localStorage.getItem(this.localanswerName))},saveAnswer:function(e){localStorage.setItem(this.localanswerName,JSON.stringify(e))},saveTime:function(){var e=this,t=localStorage.getItem(this.localuseTime)||0;this.usedTime=setInterval((function(){localStorage.setItem(e.localuseTime,++t)}),1e3)},clearTime:function(){clearInterval(this.usedTime),this.usedTime=null},submitpaper:function(){var e=this,t=0,i="题目已经做完，确认提交吗?",s=JSON.parse(JSON.stringify(this.answer));Object.keys(s).forEach((function(e){s[e]=s[e].filter((function(e){return""!==e})),0===s[e].length&&t++})),t>0&&(i="还有".concat(t,"题未做，确认提交吗？")),n.a.confirm({title:"提交",cancelButtonText:"立即提交",confirmButtonText:"检查一下",message:i}).then((function(){return e.cardShow=!0,!1})).catch((function(){e.clearTime(),e.submitExercise(s).then((function(e){return!0})).catch((function(e){return!1}))}))},submitExercise:function(e){var t=this;e||(e=JSON.parse(JSON.stringify(this.answer)),Object.keys(e).forEach((function(t){e[t]=e[t].filter((function(e){return""!==e}))})));var i={answer:e,exerciseId:this.$route.query.exerciseId,userId:this.user.id,exerciseResultId:this.exercise.id};return new Promise((function(e,n){t.handExercisedo(i).then((function(i){t.isHandExercise=!0,e(),t.showResult()})).catch((function(i){var a=s.a.fail(i.message);"4036705"==i.code?setTimeout((function(){t.isHandExercise=!0,a.clear(),e(),t.showResult()}),2e3):n()}))}))},showResult:function(){this.$router.replace({name:"exerciseResult",query:{exerciseId:this.$route.query.exerciseId,exerciseResultId:this.exercise.id,taskId:this.$route.query.targetId,backUrl:m,courseId:this.$route.query.courseId}})},toIntro:function(){this.$router.replace({name:"exerciseIntro",query:{courseId:this.$route.query.courseId,taskId:this.$route.query.targetId,backUrl:m}})}})},p=i("0c7c"),v=Object(p.a)(g,(function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"paper-swiper"},[e.isLoading?i("e-loading"):e._e(),e.info.length>0?i("item-bank",{attrs:{current:e.cardSeq,info:e.info,answer:e.answer,"show-score":!1,"slide-index":e.slideIndex,all:e.info.length},on:{"update:current":function(t){e.cardSeq=t},"update:answer":function(t){e.answer=t},"update:slideIndex":function(t){e.slideIndex=t},"update:slide-index":function(t){e.slideIndex=t}}}):e._e(),i("guide-page"),i("div",{staticClass:"paper-footer"},[i("div",[i("span",{on:{click:function(t){e.cardShow=!0}}},[i("i",{staticClass:"iconfont icon-Questioncard"}),e._v("\n        题卡\n      ")])]),i("div",[i("span",{on:{click:e.submitpaper}},[i("i",{staticClass:"iconfont icon-submit"}),e._v("\n        提交\n      ")])])]),i("van-popup",{attrs:{position:"bottom"},model:{value:e.cardShow,callback:function(t){e.cardShow=t},expression:"cardShow"}},[e.info.length>0?i("div",{staticClass:"card"},[i("div",{staticClass:"card-title"},[i("div",[i("span",{staticClass:"card-finish"},[e._v("已完成")]),i("span",{staticClass:"card-nofinish"},[e._v("未完成")])]),i("i",{staticClass:"iconfont icon-no",on:{click:function(t){e.cardShow=!1}}})]),i("div",{staticClass:"card-list"},[i("div",{staticClass:"card-homework-item"},[i("div",{staticClass:"card-item-list"},e._l(e.info,(function(t){return i("div",{key:t.id,class:["list-cicle",1==e.formatStatus(t.type,t.id)?"cicle-active":""],on:{click:function(i){return e.slideToNumber(t.seq)}}},[e._v(e._s(t.seq))])})),0)])])]):e._e()])],1)}),[],!1,null,null,null);t.default=v.exports}}]);