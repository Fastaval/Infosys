import{j as z,k as A,r,o as H,b as c,c as y,d as D,f as n,w as b,l as N,g as R,t as u,e as t,h as F,F as G,m as J,n as Q,p as W,i as X,_ as Y}from"./index-1a4bfad1.js";import{g as Z,a as ee,b as te,u as le,d as ae}from"./users.service-8aef62a1.js";const s=x=>(W("data-v-9a185b34"),x=x(),X(),x),oe={style:{display:"flex"}},se={style:{width:"100%"}},ne={class:"ticket-actions"},ie=s(()=>t("span",null,"Oprettet:",-1)),ue=s(()=>t("span",null,"Ændret:",-1)),de=s(()=>t("span",null,"Prioritet:",-1)),pe=s(()=>t("span",null,"Oprettet af:",-1)),re=s(()=>t("span",null,"Udføres af:",-1)),ce={class:"card-body d-flex"},ve={class:"ticket-body w-100"},me=s(()=>t("div",{class:"d-flex align-items-center pb-2"},[t("h5",{class:"ps-3 mb-1"})],-1)),_e=s(()=>t("p",null,null,-1)),be={style:{color:"#ccc"}},ke=s(()=>t("h4",null,"Rediger opgave",-1)),ge={style:{display:"grid","grid-template-columns":"minmax(400px, 800px) minmax(150px, 300px)",gap:"1rem"}},fe={style:{display:"flex","flex-direction":"column"}},ye=s(()=>t("label",{class:"help-text"},"Navn/Titel",-1)),xe=s(()=>t("label",{class:"help-text"},"Opgavebeskrivelse",-1)),he={style:{display:"flex","flex-direction":"column","background-color":"#eee",padding:"1rem",height:"min-content","padding-bottom":"1.5rem","border-radius":"4px"}},Ve=s(()=>t("label",{class:"help-text"},"Status",-1)),we=s(()=>t("label",{class:"help-text"},"Kategori",-1)),Te=s(()=>t("label",{class:"help-text"},"Prioritet",-1)),Le=s(()=>t("label",{class:"help-text"},"Udføres af",-1)),Ue={style:{display:"inline-flex",gap:"1rem"}},Ce=z({__name:"Ticket",setup(x){const C=A(),a=r(),o=r(),O=r(),k=r(!1),S=r(!1);let B=[{label:"Lukket",options:[{label:"Annulleret",open:0,status:0},{label:"Godkendt",open:0,status:1},{label:"Kan/vil ikke lave",open:0,status:2}]},{label:"Åben",options:[{label:"Oprettet",open:1,status:0},{label:"Startet",open:1,status:1},{label:"Skal testes",open:1,status:2},{label:"Venter",open:1,status:3}]}];const g=r(),m=r(),d=r(),E=()=>{var h,V,_,w,T,L,v,U,f,e;const p=(a==null?void 0:a.value.open)===1?(V=(h=d.value.tickets)==null?void 0:h.open)==null?void 0:V.da:(w=(_=d.value.tickets)==null?void 0:_.closed)==null?void 0:w.da,l=a.value.open===1?(v=(L=(T=d.value.tickets)==null?void 0:T.status)==null?void 0:L.open[a.value.status])==null?void 0:v.da:(e=(f=(U=d.value.tickets)==null?void 0:U.status)==null?void 0:f.closed[a.value.status])==null?void 0:e.da;return`${p} - ${l}`},j=()=>{o.value={...o.value,open:g.value.open,status:g.value.status}},q=async()=>{S.value=!0,await le(o.value).then(async()=>{await $(),k.value=!1,S.value=!1})},K=()=>{o.value={...a.value},k.value=!0,g.value=B[a.value.open].options[a.value.status]},$=async()=>await ae(C.params.id).then(p=>a.value=p.tickets[C.params.id]),P=()=>!o.value.name||!o.value.description||!o.value.assignee;return H(async()=>{await Z().then(p=>d.value=p),m.value=Object.values(await ee()),await $(),await te(C.params.id).then(p=>O.value=p.messages)}),(p,l)=>{var f;const h=c("RouterLink"),V=c("Chip"),_=c("Button"),w=c("Card"),T=c("InputText"),L=c("Textarea"),v=c("Dropdown"),U=c("Dialog");return y(),D(G,null,[n(h,{to:"/",style:{"margin-bottom":"1rem",display:"inline-flex"}},{default:b(()=>[N("← Tilbage ")]),_:1}),m.value&&a.value?(y(),R(w,{key:0},{title:b(()=>{var e;return[N(u((e=a.value)==null?void 0:e.name)+" ",1),n(V,{class:"chip",label:E()},null,8,["label"]),n(_,{style:{float:"right"},icon:"pi pi-pencil",rounded:"",onClick:l[0]||(l[0]=i=>K())})]}),content:b(()=>{var e,i,M;return[t("div",oe,[t("div",se,u(((e=a.value)==null?void 0:e.description)??"Ingen beskrivelse"),1),t("div",ne,[ie,t("span",null,u(a.value.created?new Date(a.value.created*1e3).toLocaleString():"Ukendt"),1),ue,t("span",null,u(a.value.last_edit?new Date(a.value.last_edit*1e3).toLocaleString():"-"),1),de,t("span",null,u(((M=(i=d.value)==null?void 0:i.tickets)==null?void 0:M.priority[a.value.priority].da)??"Ukendt"),1),pe,t("span",null,u(m.value.find(I=>I.id===a.value.creator).name??"Ukendt"),1),re,t("span",null,u(m.value.find(I=>I.id===a.value.assignee).name??"Ukendt"),1)])])]}),_:1})):F("",!0),t("div",ce,[t("div",ve,[me,_e,t("div",null,[(y(!0),D(G,null,J((f=O.value)==null?void 0:f.slice().reverse(),e=>{var i;return y(),D("div",{class:"card shadow-sm p-2 mt-3",key:e.id},[t("span",be,"Oprettet: "+u(new Date(e.posted*1e3).toLocaleString())+" | "+u((i=m.value[e.user])==null?void 0:i.name),1),t("span",null,u(e==null?void 0:e.message),1)])}),128))])])]),o.value?(y(),R(U,{key:1,visible:k.value,"onUpdate:visible":l[10]||(l[10]=e=>k.value=e),modal:"",class:"newTicket"},{header:b(()=>[ke]),footer:b(()=>[t("div",Ue,[n(_,{severity:"info",text:"",label:"Luk",icon:"pi pi-times",onClick:l[8]||(l[8]=e=>k.value=!1)}),n(_,{label:"Gem",raised:"",disabled:P(),onClick:l[9]||(l[9]=e=>q()),loading:S.value},null,8,["disabled","loading"])])]),default:b(()=>[t("div",ge,[t("div",fe,[ye,n(T,{modelValue:o.value.name,"onUpdate:modelValue":l[1]||(l[1]=e=>o.value.name=e),modelModifiers:{trim:!0},required:"",style:{width:"400px"}},null,8,["modelValue"]),xe,n(L,{modelValue:o.value.description,"onUpdate:modelValue":l[2]||(l[2]=e=>o.value.description=e),modelModifiers:{trim:!0},autoResize:!0,rows:10,style:{width:"100%"},required:""},null,8,["modelValue"])]),t("div",he,[Ve,n(v,{class:"status-select",modelValue:g.value,"onUpdate:modelValue":l[3]||(l[3]=e=>g.value=e),options:Q(B),optionLabel:"label",optionGroupLabel:"label",optionGroupChildren:"options",onChange:l[4]||(l[4]=e=>j())},null,8,["modelValue","options"]),we,n(v,{modelValue:o.value.category,"onUpdate:modelValue":l[5]||(l[5]=e=>o.value.category=e),options:d.value.tickets.category.map((e,i)=>({label:e.da,value:i})),optionLabel:"label",optionValue:"value"},null,8,["modelValue","options"]),Te,n(v,{modelValue:o.value.priority,"onUpdate:modelValue":l[6]||(l[6]=e=>o.value.priority=e),options:d.value.tickets.priority.map((e,i)=>({label:e.da,value:i})),optionLabel:"label",optionValue:"value"},null,8,["modelValue","options"]),Le,n(v,{modelValue:o.value.assignee,"onUpdate:modelValue":l[7]||(l[7]=e=>o.value.assignee=e),options:m.value,resetFilterOnHide:!0,optionLabel:"name",optionValue:"id",filter:""},null,8,["modelValue","options"])])])]),_:1},8,["visible"])):F("",!0)],64)}}});const De=Y(Ce,[["__scopeId","data-v-9a185b34"]]);export{De as default};
