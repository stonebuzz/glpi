/**
 * This array is used to remember mark status of rows in browse mode
 */
var marked_row = new Array;

var timeoutglobalvar;


/**
 * modifier la propriete display d'un element
 *
 * @param objet
 * @param statut
**/
function setdisplay(objet, statut) {

   var e = objet;
   if (e.style.display != statut) {
      e.style.display = statut;
   }
   return true;
}


/**
 * tester le type de navigateur
**/
function isIe() {

   var ie     = false;
   var appVer = navigator.appVersion.toLowerCase();
   var iePos  = appVer.indexOf('msie');

   if (iePos != -1) {
      var is_minor = parseFloat(appVer.substring(iePos+5,appVer.indexOf(';',iePos)));
      var is_major = parseInt(is_minor);
   }
   if (navigator.appName.substring(0,9) == "Microsoft") {
      // Check if IE version is 6 or older
      if (is_major <= 6) {
         ie = true;
      }
   }
   return ie;
}


/**
 * @param id
**/
function cleandisplay(id) {

   var e = document.getElementById(id);
   if (e) {
      setdisplay(e,'block');
      if (isIe()) {
         doHideSelect(e);
      }
   }
}


/**
 * @param id
**/
function cleanhide(id) {

   var e = document.getElementById(id);
   if (e) {
      if (isIe()) {
         doShowSelect(e);
      }
      setdisplay(e,'none');
   }
}


/**
 * @param id
**/
function completecleandisplay(id) {

   var e = document.getElementById(id);
   if (e) {
      setdisplay(e,'block');

/* if(document.getElementById('show_entities')){
      var oneTime=0;
      var divHeight = document.getElementById('show_entities').offsetHeight;
      var divWidth = document.getElementById('show_entities').offsetWidth;

      if (divHeight>300){


         document.getElementById('show_entities').style.overflow = 'auto';
         document.getElementById('show_entities').style.height = '400px';
         // document.getElementById('show_entities').style.width =  divWidth + 'px';
         document.getElementById('show_entities').style.width =  '300px';

      }



   }
*/


      if (isIe()) {
         e.onmouseleave = function(){ completecleanhide(id) };
         hideSelect(0,0,document.documentElement.clientWidth,document.documentElement.clientHeight);
      } else {
         e.onmouseout = function(){ completecleanhide(id) };
      }
   }
}


/**
 * @param id
**/
function completecleanhide(id) {

   var e = document.getElementById(id);
   if (e) {
      setdisplay(e,'none');
      if (isIe()) {
         showSelect(0,0,document.documentElement.clientWidth,document.documentElement.clientHeight);
      }
   }
}


/**
 * effacer tous les menus du menu principal
 * afficher les select du document
 *
 * @param idMenu
**/
function hidemenu(idMenu) {

   var e = document.getElementById(idMenu);
   var e = e.getElementsByTagName('ul');

   for (var i=0 ; i<e.length ; i++) {
      if (e[i]) {
         if (isIe()) {
            doShowSelect(e[i]);
         }
         setdisplay(e[i],'none');
      }
   }
}


/**
 * masquer le menu actif par timeout
 *
 * @param idMenu
**/
function afterView(idMenu) {

   setdisplay(idMenu,'none');
   if (isIe()) {
      doShowSelect(idMenu);
   }
}


/**
 * execute la fonction showSelect
 *
 * @param objet
**/
function doShowSelect(objet) {

   if (objet) {
      //correction du bugg sur IE
      if (isIe()) {
         if (setdisplay(objet,'block')) {
            var selx = 0;
            var sely = 0;
            var selp;
            selx = getLeft(objet);
            sely = getTop(objet);
            selw = objet.offsetWidth;
            selh = objet.offsetHeight;
            showSelect(selx,sely,selw,selh);
         }
         if (setdisplay(objet,'none')){
            return true;
         }
      }
   }
}


/**
 * affiche les select du document
 *
 * @param x
 * @param y
 * @param w
 * @param h
**/
function showSelect(x,y,w,h) {

   var selx,sely,selw,selh;
   var sel = document.getElementsByTagName("SELECT");

   for (var i=0 ; i<sel.length ; i++) {
      selx = 0;
      sely = 0;
      var selp;
      selx = getLeft(sel[i]);
      sely = getTop(sel[i]);
      selw = sel[i].offsetWidth;
      selh = sel[i].offsetHeight;
      // || Manage position error computation
      if ((((selx + selw) > x)
           && (selx < (x + w))
           && ((sely + selh) > y)
           && (sely < (y + h)))
          || (selx < 0)
          || (sely < 0)) {
         sel[i].style.visibility="visible";
      }
   }
   return true;
}


/**
 * execute la fonction hideMenu
 *
 * @param objet
**/
function doHideSelect(object) {

   var e = object;
   if (isIe()) {
      var selx = 0;
      var sely = 0;
      var selp;

      selx = getLeft(e);
      sely = getTop(e);
      selw = e.offsetWidth;
      selh = e.offsetHeight;
      hideSelect(selx,sely,selw,selh);
   }
   return true;
}


/**
 * masque les select du document
 *
 * @param x
 * @param y
 * @param w
 * @param h
**/
function hideSelect(x,y,w,h) {

   var selx,sely,selw,selh,i;
   var sel = document.getElementsByTagName("SELECT");
   for (i=0 ; i<sel.length ; i++) {
      selx = 0;
      sely = 0;
      var selp;
      selx = getLeft(sel[i]);
      sely = getTop(sel[i]);
      selw = sel[i].offsetWidth;
      selh = sel[i].offsetHeight;
      // || Manage position error computation
      if ((((selx + selw) > x)
           && (selx < (x + w))
           && ((sely + selh) > y)
           && (sely < (y + h)))
          || (selx < 0)
          || (sely < 0 )) {
         sel[i].style.visibility="hidden";
      }
   }
   return true;
}


/**
 * @param id
 * @param idMenu
**/
function menuAff(id,idMenu){

   var m    = document.getElementById(idMenu);
   var item = m.getElementsByTagName('li');
   for (var i=0 ; i<item.length ; i++) {
      if (item[i].id == id) {
         var ssmenu = item[i];
      }
   }
   var m = m.getElementsByTagName('ul');
   if (isIe()) {
      //masquage des elements select du document
      if (m) {
         for (var i=1 ; i<10 ; i++) { //probleme dans le listage et le nomage des menus xhtml
            //listage des elements li nommes du type smenu + i
            var e = document.getElementById('menu'+i);
            if (e) {
               var smenu = e.getElementsByTagName('ul');
               doShowSelect(smenu[0]);
            }
         }
      }
   }

   if (ssmenu) {
      var smenu = ssmenu.getElementsByTagName('ul');
      if (smenu) {
         //masquer tous les menus ouverts
         for (var i=0 ; i<m.length ; i++) {
            setdisplay(m[i],'none');
         }
         setdisplay(smenu[0],'block');
         clearTimeout(timeoutglobalvar);
         //timeoutglobalvar = setTimeout(function(){afterView(smenu[0])},1000);
         if (isIe()) {
            ssmenu.onmouseleave = function(){ timeoutglobalvar = setTimeout(function(){afterView(smenu[0])},300); };
            doHideSelect(smenu[0]);
         } else {
            ssmenu.onmouseout = function(){ timeoutglobalvar = setTimeout(function(){afterView(smenu[0])},300); };
         }
      }
   }
}


/**
 * @param URL_List
**/
function jumpTo(URL_List) {
   var URL = URL_List.options[URL_List.selectedIndex].value;  window.location.href = URL;
}


browserName = navigator.appName;
browserVer  = parseInt(navigator.appVersion);
if (((browserName == "Netscape") && (browserVer >= 3))
    || ((browserName == "Microsoft Internet Explorer") && (browserVer >= 4))){
   version = "n3";
} else {
   version = "n2";
}


function historyback() {
   history.back();
}


function historyforward() {
   history.forward();
}


/**
 * @param Type
 * @param Id
**/
function fillidfield(Type,Id) {

   window.opener.document.forms["helpdeskform"].elements["items_id"].value = Id;
   window.opener.document.forms["helpdeskform"].elements["itemtype"].value = Type;
   window.close();
}

/**
 * marks all checkboxes inside the given element
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    container_id    DOM element
**/
function markCheckboxes(container_id) {

   var checkboxes = document.getElementById(container_id).getElementsByTagName('input');
   for (var j=0 ; j<checkboxes.length ; j++ ) {
      checkbox = checkboxes[j];
      if (checkbox && (checkbox.type == 'checkbox')) {
         if (checkbox.disabled == false ) {
            checkbox.checked = true;
         }
      }
   }
   return true;
}


/**
 * marks all checkboxes inside the given element
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    container_id    DOM element
**/
function unMarkCheckboxes(container_id) {

   var checkboxes = document.getElementById(container_id).getElementsByTagName('input');
   for (var j=0 ; j<checkboxes.length ; j++ ) {
      checkbox = checkboxes[j];
      if (checkbox && (checkbox.type == 'checkbox')) {
         checkbox.checked = false;
      }
   }
   return true;
}


/**
 * toggle all checkboxes inside the given element
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    container_id    DOM element
**/
function toggleCheckboxes( container_id ) {

   var checkboxes = document.getElementById(container_id).getElementsByTagName('input');
   for (var j=0 ; j<checkboxes.length ; j++ ) {
      checkbox = checkboxes[j];
         if (checkbox && (checkbox.type == 'checkbox')) {
            if (checkbox.disabled == false) {
               if (checkbox.checked == false) {
                  checkbox.checked = true;
               } else {
                  checkbox.checked = false;
               }
            }
         }
   }
   return true;
}

/**
 * display "other" text input field in case of selecting "other" option
 *
 * @since version 0.84
 *
 * @param    select_object     DOM select object
 * @param    other_option_name the name of both the option and the text input field
**/
function displayOtherSelectOptions(select_object, other_option_name) {

   var SelIndex = select_object.selectedIndex;
   if (select_object.options[select_object.selectedIndex].value == other_option_name) {
      document.getElementById(other_option_name).style.display = "inline";
   } else {
      document.getElementById(other_option_name).style.display = "none";
   }
   return true;
}





/**
 * Check all checkboxes inside the given element as the same state as a reference one (toggle this one before)
 * the given element is usaly a table or a div containing the table or tables
 *
 * @param    reference_id    DOM element
 * @param    container_id    DOM element
**/
function checkAsCheckboxes( reference_id, container_id ) {
   $('#' + container_id + ' input[type="checkbox"]:enabled')
      .prop('checked', $('#' + reference_id).is(':checked'));

   return true;
}

/**
 * Permit to use Shift key on a group of checkboxes
 * Usage: $form.find('input[type="checkbox"]').shiftSelectable();
 */
$.fn.shiftSelectable = function() {
   var lastChecked,
       $boxes = this;

   // prevent html selection
   document.onkeydown = function(e) {
      var keyPressed = e.keyCode;
      if (keyPressed == 16) { // shift key
         $('html').addClass('unselectable');
         document.onkeyup = function() {
            $('html').removeClass('unselectable');
         };
      }
   };

   $($boxes.selector).parent().click(function(evt) {
      if ($boxes.length <= 0) {
         $boxes = $($boxes.selector);
      }
      var selected_checkbox = $(this).children('input[type=checkbox]');

      if(!lastChecked) {
         lastChecked = selected_checkbox;
         return;
      }

      if(evt.shiftKey) {
         evt.preventDefault();
         var start = $boxes.index(selected_checkbox),
             end = $boxes.index(lastChecked);
         $boxes.slice(Math.min(start, end), Math.max(start, end) + 1)
               .prop('checked', $(lastChecked).is(':checked'))
               .trigger('change');
      }

      lastChecked = selected_checkbox;
   });
};


/**
 * @param text
 * @param where
**/
function confirmAction(text, where){

   if (confirm(text)) {
      window.location = where;
   }
}


/**
 * Fonction permettant de connaître la position d'un objet par rapport au bord gauche de la page.
 * Cet objet peut être à l'intérieur d'un autre objet.
 *
 * @param MyObject
 *
 * @returns la distance par rapport au bord gauche de la page
**/
function getLeft(MyObject){

   if (MyObject.offsetParent) {
      return (MyObject.offsetLeft + getLeft(MyObject.offsetParent));
   }
   return (MyObject.offsetLeft);
}


/**
 * Fonction permettant de connaître la position d'un objet par rapport au bord haut de la page.
 * Cet objet peut être à l'intérieur d'un autre objet.
 *
 * @param MyObject
 *
 * @returns la distance par rapport au bord haut de la page
**/
function getTop(MyObject){

   if (MyObject.offsetParent) {
      return (MyObject.offsetTop + getTop(MyObject.offsetParent));
   }
   return (MyObject.offsetTop);
}


/**
 * safe function to hide an element with a specified id
 *
 * @param id               id of the dive
 * @param img_name         name attribut of the img item
 * @param img_src_close    url of the close img
 * @param img_src_open     url of the open img
**/
function showHideDiv(id, img_name, img_src_close, img_src_open) {

   if (document.getElementById) { // DOM3 = IE5, NS6
      if (document.getElementById(id).style.display == 'none') {
         document.getElementById(id).style.display = 'block';
         if (img_name!='') {
            document[img_name].src=img_src_open;
         }
      } else {
         document.getElementById(id).style.display = 'none';
         if (img_name!='') {
            document[img_name].src=img_src_close;
         }
      }
   } else {
      if (document.layers) { // Netscape 4
         if (document.id.display == 'none') {
            document.id.display = 'block';
            if (img_name != '') {
               document[img_name].src=img_src_open;
            }
         } else {
            document.id.display = 'none';
            if (img_name != ''){
               document[img_name].src=img_src_close;
            }
         }
      } else { // IE 4
         if (document.all.id.style.display == 'none') {
            document.all.id.style.display = 'block';
            if (img_name != '') {
               document[img_name].src=img_src_close;
            }
         } else {
            document.all.id.style.display = 'none';
            if (img_name != '') {
               document[img_name].src=img_src_close;
            }
         }
      }
   }
}


/**
 * safe function to hide an element with a specified id
 *
 * @param id
 * @param img_name
 * @param img_src_yes
 * @param img_src_no
**/
function toogle(id, img_name, img_src_yes, img_src_no) {

   if (document.getElementById) { // DOM3 = IE5, NS6
      if (document.getElementById(id).value == '0') {
         document.getElementById(id).value = '1';
         if (img_name != '') {
            document[img_name].src=img_src_yes;
         }
      } else {
         document.getElementById(id).value = '0';
         if (img_name != '') {
            document[img_name].src=img_src_no;
         }
      }
   }
}


/**
 * @since version 0.84
 *
 * @param tbl
 * @param img_name
 * @param img_src_close
 * @param img_src_open
 */
function toggleTableDisplay(tbl,img_name,img_src_close,img_src_open) {

   var tblRows = document.getElementById(tbl).rows;
   for (i=0 ; i < tblRows.length ; i++) {
      if (tblRows[i].className.indexOf("headerRow") == -1) {
         if (tblRows[i].style.display == 'none') {
            tblRows[i].style.display = "table-row";
            if (img_name != ''){
               document[img_name].src = img_src_open;
            }

         } else {
            tblRows[i].style.display = "none";
            if (img_name != ''){
               document[img_name].src = img_src_close;
            }
         }
      }
   }
   if (document.getElementById(tbl+'2')) {
      toggleTableDisplay(tbl+'2','');
   }
   if (document.getElementById(tbl+'3')) {
      toggleTableDisplay(tbl+'3','');
   }
   if (document.getElementById(tbl+'4')) {
      toggleTableDisplay(tbl+'4','');
   }
   if (document.getElementById(tbl+'5')) {
      toggleTableDisplay(tbl+'5','');
   }
}


/**
 * @since version 0.84
 *
 * @param target
 * @param fields
**/
function submitGetLink(target,fields) {

    var myForm    = document.createElement("form");
    myForm.method = "post" ;
    myForm.action = target ;
    for (var name in fields) {
        var myInput = document.createElement("input") ;
        myInput.setAttribute("name", name) ;
        myInput.setAttribute("value", fields[name]);
        myForm.appendChild(myInput) ;
    }
    document.body.appendChild(myForm) ;
    myForm.submit() ;
    document.body.removeChild(myForm) ;
}


/**
 * @since version 0.85
 *
 * @param id
**/
function selectAll(id) {
   var element =$('#'+id);var selected = [];
   element.find('option').each(function(i,e){
      selected[selected.length]=$(e).attr('value');
   });
   element.select2('val', selected);
}

/**
 * @since version 0.85
 *
 * @param id
**/
function deselectAll(id) {
   $('#'+id).val('').trigger('change');
}


/**
 * Set all the checkbox that refere to the criterion
 *
 * @since version 0.85
 *
 * @param criterion jquery criterion
 * @param reference the new reference object, boolean, id ... (default toggle)
 *
**/
function massiveUpdateCheckbox(criterion, reference) {
    if (typeof(reference) == 'undefined') {
        var value = null;
    } else if (typeof(reference) == 'boolean') {
        var value = reference;
    } else if (typeof(reference) == 'string') {
        var value = $('#' + reference).prop('checked');
    } else if (typeof(reference) == 'object') {
        var value = $(reference).prop('checked');
    }
    if (typeof(value) == 'undefined') {
        return false;
    }
    $(criterion).each(function() {
        if (typeof(reference) == 'undefined') {
            value = !$(this).prop('checked');
        }
        $(this).prop('checked', value);
    });
    return true;
}


$(function(){

        $("body").delegate('td','mouseover mouseleave', function(e) {
            var col = $(this).closest('tr').children().index($(this));
            var tr = $(this).closest('tr');
            if (!$(this).closest('tr').hasClass('noHover')) {
            if (e.type == 'mouseover') {
               tr.addClass("rowHover");
               // If rowspan
               if (tr.has('td[rowspan]').length == 0) {

                     tr.prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').addClass("rowHover");
               }

               $(this).closest('table').find('tr:not(.noHover) th:nth-child('+(col+1)+')').addClass("headHover");
            } else {
               tr.removeClass("rowHover");
               // remove rowspan
               tr.removeClass("rowHover").prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').removeClass("rowHover");
               $(this).closest('table').find('tr:not(.noHover) th:nth-child('+(col+1)+')').removeClass("headHover");
            }
            }
        });

});

//Hack for Jquery Ui Date picker
var _gotoToday = jQuery.datepicker._gotoToday;
jQuery.datepicker._gotoToday = function(a){
   var target = jQuery(a);
   var inst = this._getInst(target[0]);
   _gotoToday.call(this, a);
   jQuery.datepicker._selectDate(a, jQuery.datepicker._formatDate(inst,inst.selectedDay, inst.selectedMonth, inst.selectedYear));
};



/* TImeline for itiobjects */

filter_timeline = function() {
   $(document).on("click", '.filter_timeline li a', function(event) {
      //hide all elements in timeline
      $('.h_item').addClass('h_hidden');

      //reset all elements
      if ($(this).hasClass('reset')) {
         $('.filter_timeline li a img').each(function(el2) {
            $(this).attr('src', $(this).attr('src').replace('_active', ''));
         })
         $('.h_item').removeClass('h_hidden');
         return;
      }

      //activate clicked element
      var current_el = $(this).children('img');
      $(this).toggleClass('h_active');
      if (current_el.attr('src').indexOf('active') > 0) {
         current_el.attr('src',  current_el.attr('src').replace('_active', ''));
      } else {
         current_el.attr('src', current_el.attr('src').replace(/\.(png)$/, '_active.$1'));
      }

      //find active classname
      active_classnames = [];
      $('.filter_timeline .h_active').each(function(index) {
         active_classnames.push(".h_content."+$(this).attr('class').replace(' h_active', ''));
      })

      $(active_classnames.join(', ')).each(function(index){
         $(this).parent().removeClass('h_hidden');
      })

      //show all items when no active filter
      if (active_classnames.length == 0) {
         $('.h_item').removeClass('h_hidden');
      }
   });
}


read_more = function() {
   $(document).on("click", ".long_text .read_more a", function(event) {
      $(this).parents('.long_text').removeClass('long_text');
      $(this).parent('.read_more').remove();
      return false;
   });
}


var split_button_fct_called = false;
split_button = function() {
   if (split_button_fct_called) {
      return true;
   }
   split_button_fct_called = true

   // unfold status list
   $(document).on("click", '.x-button-drop', function(event) {
      $(this).parents(".x-split-button").toggleClass('open');
   });

   $(document).on("click", '.x-split-button', function(event) {
      event.stopPropagation();
   });

   //click on an element of status list
   $(document).on("click", '.x-button-drop-menu li', function(event) {
      if (event.target.children.length) {
         var xBtnDrop = $(this).parent().siblings(".x-button-drop");
         //clean old status class
         xBtnDrop.attr('class','x-button x-button-drop');

         //find status
         match = event.target.children[0].src.match(/.*\/(.*)\.png/);
         cstatus = match[1];

         //add status to dropdown button
         xBtnDrop.addClass(cstatus);

         //fold status list
         $(this).parents(".x-split-button").removeClass('open');
      }
   });

   //fold status list on click on document
   $(document).on("click", function(event) {
      if ($('.x-split-button').hasClass('open')) {
         $('.x-split-button').removeClass('open');
      }
   });
}

// Responsive header
if ($(window).width() <= 700) {
   var didScroll;
   var lastScrollTop = 0;
   var delta = 5;
   var navbarHeight = $('header').outerHeight();

   $(window).scroll(function(event){
      didScroll = true;
   });

   setInterval(function() {
      if (didScroll) {
         scollHeaderResponsive();
         didScroll = false;
      }
   }, 250);

   var scollHeaderResponsive = function() {
      var st = $(this).scrollTop();

      // Make sure they scroll more than delta
      if(Math.abs(lastScrollTop - st) <= delta)
         return;

      if (st > lastScrollTop && st > navbarHeight){
         // Scroll Down
         $('#header').removeClass('nav-down').addClass('nav-up');
      } else {
         // Scroll Up
         if(st + $(window).height() < $(document).height()) {
            $('#header').removeClass('nav-up').addClass('nav-down');
         }
      }
      lastScrollTop = st;
   }
}


// prevent jquery ui dialog to keep focus
$(function(){
   $.ui.dialog.prototype._focusTabbable = function(){};
});



/**
 * Uploads a file receive on tinyMCE to Server
 *
 * @param      {FormData}             Object FormData with file (blob format inside)
 * @param      {Object}               editor  instance of editor (TinyMCE)
 * @param      {Boolean}              isImage   
 * @return     {String}               Tag of file
 */
function uploadFile(fd,editor,isImage){ // upload function

   returnS = false;

   $.ajax({ // JQuery Ajax
      type: 'POST',
      url: '../ajax/uploadfile.php', 
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'JSON',
      async: false,
      success: function(data) {
          $.each(data, function(index, element) {
             if(element[0].error == undefined){
                  
                  tag = getFileTag(element);
                  //if is an image add tag 
                  if(isImage){
                      returnS = tag.tag;
                  }
                  //display uploaded file
                  displayUploadedFile(element[0],tag,isImage,editor);

             }else{
                  returnS = false;
                  alert(element[0].error);
             }  
          });
      },

      error: function (request, status, error) {
         alert(request.responseText);
      }
   });

   return returnS;
}


/**
* Determines if data from drop is an image.
*
* @param      {<type>}   file    The file
* @return     {boolean}  True if image, False otherwise.
*/
function IsImageFromDrop(file){
   var fileType = file["type"];
   var ValidImageTypes = ["image/gif", "image/jpeg","image/jpg", "image/png"];
   if ($.inArray(fileType, ValidImageTypes) < 0) {
      return false;
   }else{
      return true;
   }
}


 /**
  * Gets the file tag.
  *
  * @param      {(boolean|string)}  data receive from uploadFile
  * @return     {(boolean|string)}  The file tag.
  */
function getFileTag(data){ 
                           
   returnString = '';

   $.ajax({ 
      type: 'POST',
      url: '../ajax/getFileTag.php', 
      data: {'data':data},
      dataType: 'JSON',
      async: false,
      success: function(data) {                   
          returnString = data[0];
      },
      error: function (request, status, error) {
          console.log(request.responseText);
          returnString=false;
      }
   });

   return returnString;
}


/**
 * { function_description }
 *
 * @param      {JSON}    file       The file
 * @param      {string}  tag        The tag
 * @param      {Boolean} IsImage    The image
 * @param      {Object}  editor     The editor instance
 */
function displayUploadedFile(file, tag , IsImage,editor){

   //manage input name switch file type
   name = '_stock_image';
   if(!IsImage){
      name = '_filename';
      file.id = file.id.replace('docstock_image','docfilename');
   }

   rand = Math.random();


   var p = $('<p/>').attr('id',file.id).html('<b>Fichier : </b>'+file.display+' <b>Tag : </b>'+tag.tag+' ').appendTo('#fileupload_info');
   var p2 = $('<p/>').attr('id',file.id+'2').css({'display':'none'}).appendTo('#fileupload_info');

   // File
   $('<input/>').attr('type', 'hidden').attr('name', name+'['+fileindex+']').attr('value',file.name).appendTo(p);
   // Tag
   $('<input/>').attr('type', 'hidden').attr('name', '_tag'+name+'['+fileindex+']').attr('value', tag.name).appendTo(p);

   // Delete button
   var elementsIdToRemove = {0:file.id, 1:file.id+'2'};
   $('<img src="../pics/delete.png" class="pointer">').click(function(){
   deleteImagePasted(elementsIdToRemove, tag.tag,editor);
   }).appendTo(p);

   fileindex++;
}

/**
 * Remove image pasted or droped
 *
 * @param      {<type>}  elementsIdToRemove  The elements identifier to remove
 * @param      {string}  tagToRemove         The tag to remove
 * @param      {<type>}  editor              The editor
 */
function deleteImagePasted(elementsIdToRemove, tagToRemove,editor){
   // Remove file display lines
   $.each(elementsIdToRemove, function (index, id) {
      $('#'+id).remove();
   });

   editor.setContent(editor.getContent().replace('<p>'+tagToRemove+'</p>', ''));

   var re = new RegExp('#', 'g');
   editor.dom.remove(tagToRemove.replace(re,''));
}


function insertImgFromFile(editor,fileImg,tag){

  var urlCreator = window.URL || window.webkitURL;
  var imageUrl = urlCreator.createObjectURL(fileImg);

  var re = new RegExp('#', 'g');
  editor.execCommand('mceInsertContent',false, "<img width=\"760\" height=\"251\" id='"+tag.replace(re,'')+"' src='"+imageUrl+"'>"); 

}


/**
 * Replace content on tinyMCE
 *
 * @param      {Object}             editor   The editor
 * @param      {String}             search   The search
 * @param      {String}             replace  The replace
 */
function replaceContent(editor,search,replace){
   if(!replace) replace = '';

   var re =/\[\*\*(.*?)\*\*\]/;
   body = editor.getContent();
   body = body.replace(re,replace);
   editor.setContent(body);
}

/**
 * Sets the cursor at the end.
 *
 * @param      {<type>}  editor  The editor
 */
function setCursorAtTheEnd(editor){
   body = editor.getContent();
   body +='<p> </p>';
   editor.setContent(body);
   editor.dom.add(editor.getBody(),'p');
   editor.selection.select(editor.getBody(), true); // ed is the editor instance
   editor.selection.collapse(false);
}


/**
 * Convert dataURI to BLOB
 *
 * @param      {<type>}  dataURI  The data uri
 * @return     {Blob}    { description_of_the_return_value }
 */
function dataURItoBlob(dataURI) {
   // convert base64/URLEncoded data component to raw binary data held in a string
   var byteString;
   if (dataURI.split(',')[0].indexOf('base64') >= 0){
      byteString = atob(dataURI.split(',')[1]);
   }else{
      byteString = unescape(dataURI.split(',')[1]);
   }

   // separate out the mime component
   var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

   // write the bytes of the string to a typed array
   var ia = new Uint8Array(byteString.length);
   for (var i = 0; i < byteString.length; i++) {
      ia[i] = byteString.charCodeAt(i);
   }

   return new Blob([ia], {type:mimeString});
}




/**
* Function to convert File object to File Reader
*
* @param      {String}      mimeType  The mime type
* @param      {String}     base64    The base 64
* @return     {FileReader}  File object
*/
function convertFileToFileReader(file){
   var fr  = new FileReader();
   fr.fileName = file.name;
   fr.readAsDataURL(file);
   return fr;
}

/**
 * Gets the random file name.
 *
 * @return     {string}  The random file name.
 */
function getRandomFileName(){
   return 'image_paste'+ Math.floor((Math.random() * 10000000) + 1); ;
}


/**
* Function to convert base64 data to File Object
*
* @param      {String}      mimeType  The mime type
* @param      {String}     base64    The base 64
* @return     {FileReader}  File object
*/
function convertBase64ToFileReader(mimeType , base64){
   var the_file = new Blob([window.atob(base64)],  {type: mimeType, encoding: 'utf-8'});
   var fr = new FileReader();

   fr.onload = function ( oFREvent ) {
      var v = oFREvent.target.result.split(',')[1]; // encoding is messed up here, so we fix it
      v = atob(v);
      var good_b64 = btoa(decodeURIComponent(escape(v)));
   };

   fr.fileName = 'image_paste'+ Math.floor((Math.random() * 10000000) + 1); ;
   fr.readAsDataURL(the_file);

   return fr;
}


/**
* Function to check if data paste on TinyMCE is an image
*
* @param      String content  The img tag
* @return     String mimeType   return mimeType of data
*/
function isImageFromPaste(content){

   var regex1 = new RegExp('<img src="data:image\/');   
   var regex2 = new RegExp('<img alt="" src="data:image\/');   

   if(content.match(regex1) || content.match(regex2)){
      return true;
   }else{
      return false;
   }

}

/**
* Function to check if data paste on TinyMCE is an image
*
* @param      String content  The img tag
* @return     String mimeType   return mimeType of data
*/
function isImageBlobFromPaste(content){

   var regex3 = new RegExp('<img alt="" src="blob:');   
   var regex4 = new RegExp('<img src="blob:');   

   if(content.match(regex3) || content.match(regex4)){
      return true;
   }else{
      return false;
   }

}

/**
* Function to remove 'alt' attribute  
*
* @param      {string}  content  The content
* @return     {string}  content The content cleaning
*/
function cleanDataPasted(content){

   content = content.replace('alt=""', '');
   return content;

}


/**
* Function to extract mimeType data from img tag process bu TinyMCE
*
* @param      String content  The img tag
* @return     String mimeType   return mimeType of data
*/
function extractMimeTypeFromImgTag(content){

   content = extractSrcFromImgTag(content);
   type = content.split(',')[0]; //data:image/png;base64
   mimetype = type.split(';')[0];
   mimetype = mimetype.split(':')[1];
   return mimetype;

}

/**
* Function to extract base64 data from img tag process bu TinyMCE
*
* @param      String content  The img tag
* @return     String base64   return base64 data
*/
function extractBase64FromImgTag(content){

   content = extractSrcFromImgTag(content);
   base64 = content.split(',')[1];
   return base64;

}



/**
* Function to extract src tag from img tag process bu TinyMCE
*
* @param      {string}  content  The img tag
*/
function extractSrcFromImgTag(content){

   var match = content.match(/\<img.+src\=(?:\"|\')(.+?)(?:\"|\')(?:.+?)\>/)
   return match[1];

}


/**
* Function to extract src tag from img tag process bu TinyMCE
*
* @param      {string}  content  The img tag
*/
function extractSrcFromBlobImgTag(content){
   var match = content.match(/\<img[^>]+src="(blob:http%3A\/\/[^">]+)/);
      if(match == null){
         var match = content.match(/\<img[^>]+src="(blob:http:\/\/[^">]+)/);
      }

   return match[1];

}


function processFileDrop(e,editor){

    var dataTransfer = e.dataTransfer;

   e.preventDefault();
   e.stopPropagation();



   if (dataTransfer && dataTransfer.files && dataTransfer.files.length ==  1 ) {

       var files = e.dataTransfer.files;

       state_upload = '';
       state_upload += '[** UPLOADING FILE == '+files[0].name+' **]';

       editor.execCommand('mceInsertContent',false, state_upload);

       //Create formdata objet to upload object as file on ajax request
       var fd = new FormData(); 
       fd.append('file_0', files[0]); 

       //make ajax call for upload doc
       res = uploadFile(fd,editor, IsImageFromDrop(files[0]));

       //replace upload state by html render of image
       replaceContent(editor,state_upload,'');

       //Set cursor at the end
       setCursorAtTheEnd(editor);

       if(IsImageFromDrop(files[0])){
           insertImgFromFile(editor,files[0],res);
       }
       //Set cursor at the end
       setCursorAtTheEnd(editor);

   }else if(dataTransfer && dataTransfer.files && dataTransfer.files.length >  1 ){
       alert("Thanks to upload only one file");
   }

}


function processImageFromPaste(e,editor){

   //bypass propagation
   e.preventDefault();
   e.stopPropagation();

   state_upload = '';

   content = e.content;

   //extract base64 data
   base64 = extractSrcFromImgTag(content);

   //create random filename
   filename = getRandomFileName();

   //create message for wait during upload
   state_upload += '[** UPLOADING FILE == '+filename+' **]';
   editor.execCommand('mceInsertContent',false, state_upload);

   //trasnform to blob
   blob = dataURItoBlob(base64);

   //create FormData to upload object as file on ajx request
   var fd = new FormData();
   fd.append("file_0", blob,filename);

   //upload file
   res = uploadFile(fd,editor,true);

   //replace upload state by html render of image
   replaceContent(editor,state_upload,'');

   //Set cursor at the end
   setCursorAtTheEnd(editor); 

   insertImgFromFile(editor,blob,res);

   //Set cursor at the end
   setCursorAtTheEnd(editor);  

   //editor.execCommand('mceReplaceContent',false, '');
   e.content = '';
   
}


function processImageBlobFromPaste(e,editor){
   
   e.preventDefault();
   e.stopPropagation();


   content = e.content;

   filename = getRandomFileName();

   src = extractSrcFromBlobImgTag(content);

   state_upload = '';

   //create random filename
   filename = getRandomFileName();

   //create message for wait during upload
   state_upload += '[** UPLOADING FILE == '+filename+' **]';
   editor.execCommand('mceInsertContent',false, state_upload);

   var xhr = new XMLHttpRequest();
   xhr.open('GET', src, true);
   xhr.responseType = 'blob';
   xhr.onload = function(e) {

      if (this.status == 200) {

         myBlob = this.response;
         var fd = new FormData();

         fd.append("file_0", myBlob,filename+'.png');

         //upload file
         res = uploadFile(fd,editor,true);

         //replace upload state by html render of image
         replaceContent(editor,state_upload,'');

         setCursorAtTheEnd(editor);  

         insertImgFromFile(editor,blob,res);

         //Set cursor at the end
         setCursorAtTheEnd(editor);  

         //editor.execCommand('mceReplaceContent',false, '');
         e.content = '';

      }else{
         console.log("error");
      }

   };
   xhr.send();


}