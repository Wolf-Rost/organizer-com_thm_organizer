function App(s,r,w,y,q,o,v,e,b,p,a,l,c,x,f,d,i,h){var n=this;var m=null;var j=null;var k=null;var q=q;var o=o;var t=r;var g=s;var i=i;var y=y;var u=w;App.prototype.ajaxHandler=function(D,z){j=new Curriculum(s,K,L,y,q,o,v,e,b,p,a,l,c,x,f,d,i,h);k=j.addCurriculum(q,o);var K=Ext.decode(D.responseText);m=K[0].childs[0];for(var I=0,J=m.length;I<J;++I){var L=j.addSemester(m[I]);var G=m[I].childs;if(typeof G==="undefined"){continue}var A=j.addContainer(v);var H=false;var B=j.addSemesterText(m[I]);L.add(B);for(var F=0,C=G.length;F<C;++F){var E=null;if(F==0){E=j.getAsset(G[F],m[I],2,2)}else{if(H==true){E=j.getAsset(G[F],m[I],1,2);H=false}else{E=j.getAsset(G[F],m[I],2,2)}}if(A.items.length<x){A.add(E)}else{E=j.getAsset(G[F],m[I],2,2);A=j.addContainer(v);A.add(E)}L.add(A);if(G[F].asset_type_id==2&&G[F].pool_type==0){H=true}}k.add(L)}k.doLayout();var M="loading_"+i;Ext.Element.get(M).destroy();var M="curriculum_"+i;k.render(Ext.Element.get(M))};App.prototype.performAjaxCall=function(){var z="loading_"+i;var A=new Ext.create("Ext.Component",{xtype:"box",autoEl:{tag:"a",href:"",children:[{tag:"img",id:"responsible-image",cls:"tooltip",src:loading_icon,}]},renderTo:z});Ext.Ajax.request({url:"index.php?option=com_thm_organizer&task=curriculum.getJSONCurriculum&tmpl=component&id="+t+"&Itemid="+g+"&lang="+y+"&semesters="+u,method:"GET",success:n.ajaxHandler})}};