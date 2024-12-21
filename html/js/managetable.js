/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright Author Dany De Bontridder danydb@aevalys.eu
/**
 * @class
 *  Javascript object to manage ajax calls to
 * save , input or delete data row. 
 * The callback must
 respond with a XML file , the tag status for the result
 and data the HTML code to display

 List of function
 ====================
 - set_control(p_ctl_name)
 - set_callback (p_new_callback)
 - param_add (json object)
 - parseXML (private function)
 - save
 - delete
 - input
 How to call a function AFTER save ?
 You set a function afterSaveFct like in the example, it will be trigger after you submit the FORM
 a function  named afterSaveFct(r) where r is the row in HTML to display, with a attribute ctl_pk_id which is the    primary key and id of the row

 @example
 document_attach_obj.afterSaveFct=function(e) {
    var ctl_pk_id=e.getAttribute("ctl_pk_id");
  	var formData = new FormData();
    var file_data=document.getElementById('da_file_name');
    formData.append('da_file_name', file_data.files[0]);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax.php?p_id="+ctl_pk_id+"&do=upload_document", true);
	xhr.send(formData);
}
  As a hidden parameter the Manage_Table:object_name must be  set

 // the object_name is tbl6030ee4ee519e , in the table each row (TR) has an attribute ctl_pk_id which is the id (primary key)
 // <tr ctl_pk_id=""  ...> </tr>
 tbl6030ee4ee519e.afterSaveFct=function(p_param) {
  console.log(p_param);
  console.log(this)
  console.log(p_param.getAttribute(ctl_pk_id))
}
 */

/**
 * @constructor
 * @param {string} p_table_name the data table on which we're working
 in javascript , to create an object to manipulate the table
 version.
@example
// Version will manage the table "version"
 var version=new ManageTable("version");

 // the file ajax_my.php will be called
 version.set_callback("ajax_my.php");

 // Add supplemental parameter to this file
 version.param_add ({"plugin_code":"OIC"};

 // Set the id of dialog box , table and tr prefix
 version.set_control("dialbox1");

 //The answer from ajax must be like this template

    <xml>
    <ctl> id of the control to update for diag.box, row </ctl>
    <status> OK , FAIL ...</status>
    <html> Html to display</html>
    </xml>

*/
var ManageTable = function (p_table_name)
{
    this.callback = "ajax.php"; //!< File to call
    this.control = "dtr"; //<! Prefix Id of dialog box, table, row
    this.mt_style={};
    this.sort_column=0;
    this.afterSaveFct=undefined; // function to call after "save"
    this.cssclass="inner_box";
    this.param = {"table": p_table_name, "ctl_id": this.control}; //<! default value to pass
    this.set_style=function(p_json) {
        this.mt_style=p_json;
    };
    /**
     * Set the sort , 
     * @param {string} p_column  column number start from 0
     * @param {string} p_type type of sort (string, numeric)
     * @returns {ManageTable.set_sort}
     */
    this.set_sort = function (p_column) {
      
      this.sort_column=p_column;
    };

    this.set_dialog_box=function (p_dialog_box){
        this.control=p_dialog_box;
    }
    /**
     * Insert the row a the right location
     * @param {type} p_element_row DOMElement TR
     * @returns nothing
     */
    this.insertRow=function(p_table,p_element_row,sort_column) {
        try {
        // use the table
        //compute the length of row
        //if rows == 0 or the sort is not defined then append 
        if ( this.sort_column==-1 || p_table.rows.length < 2 || p_table.rows[1].cells[sort_column] == undefined || p_table.rows[1].cells[sort_column].getAttribute('sort_value') == undefined ) {
             var row=p_table.insertRow(p_table.rows.length);
            row.innerHTML=p_element_row.innerHTML;
            row.id=p_element_row.id;
            row.ctl_pk_id=row.id;
            return;
        }
        // loop for each row , compare the innerHTML of the column with the
        // value if less than insert before
        var i = 0;
        for (i = 1;i<p_table.rows.length;i++) {
            var table_value=p_table.rows[i].cells[sort_column].getAttribute('sort_value') ;
            var element_value=p_element_row.cells[sort_column].getAttribute('sort_value');
            
                // if both are numeric so we force a numeric comparison
            if ( ! isNaN(parseFloat(table_value))  && ! isNaN(parseFloat(element_value)) ) {
                table_value=parseFloat(table_value);
                element_value=parseFloat(element_value);
            }
            
            if (table_value > element_value) {
                
                var row=p_table.insertRow(i);
                row.innerHTML=p_element_row.innerHTML;
                row.id=p_element_row.id;
                row.ctl_pk_id=row.id;
                return;
            }
        }
        p_table.appendChild(p_element_row);
    } catch(e) {
        console.log("insertRow failed with "+e.message);
        throw e;
    }
        
    };
    var answer = {};
    /**
     *@fn ManageTable.set_control 
     *@brief Set the id of the control name , used as 
     * prefix for dialog box , table id and row
     *@param string p_ctl_name id of dialog box
     */
    this.set_control = function (p_ctl_name) {
        this.control = p_ctl_name;
    };
    /**
     *@brief set the name of the callback file to 
     * call by default it is ajax.php
     */
    this.set_callback = function (p_new_callback) {
        this.callback = p_new_callback;
    };
    /**
     *@brief By default send the json param variable
     * you can add a json object to it in order to 
     * send it to the callback function
     */
    this.param_add = function (p_obj) {
        var result = {};
        for (var key in this.param) {
            result[key] = this.param[key];
        }
        for (var key in p_obj) {
            result[key] = p_obj[key];
        }
        this.param = result;
        return this.param;
    };
    /**
     @brief receive answer from ajax and fill up the 
     private object "answer"
     @param req Ajax answer
     */
    this.parseXML = function (req) {
        try {
            if (req.responseText==='NOCONX') { reconnect();throw new Error("NOCONX") ;}
            var xml = req.responseXML;
            var status = xml.getElementsByTagName("status");
            var ctl = xml.getElementsByTagName("ctl");
            var html = xml.getElementsByTagName("html");
            var ctl_row = xml.getElementsByTagName("ctl_row");
            var ctl_pk_id=xml.getElementsByTagName("ctl_pk_id");
            if (status.length == 0 || ctl.length == 0 || html.length == 0)
            {
                throw content[53] + req.responseText;

            }
            var answer=[];
            answer['status'] = getNodeText(status[0]);
            answer['ctl'] = getNodeText(ctl[0]);
            answer['ctl_row'] = getNodeText(ctl_row[0]);
            answer['html'] = getNodeText(html[0]);
            answer['ctl_pk_id'] = getNodeText(ctl_pk_id[0]);
            return answer;
        } catch (e) {
            console.error("managetable:parseXML")
            throw e;
        }
    };

    /**
     *Call the ajax with the action save , it is possible to call a function after the save by setting
     * a function to afterSaveFct.As a hidden parameter the Manage_Table:object_name must be  set
     * @param form_id string id of the FORM format ("frm"+object_name+"_"+p_id)
     *
     *@example
  tbl6030f6f8c336a.afterSaveFct=function() {
    // if p_id == -1 then we are adding
    if ( this.param.p_id != -1 ) { return;}
    // retrive the id
    var id=this.new_row.id.replace('tbl6030f6f8c336a_','');
    // recall input ManageTable.input
    this.input(id,'tbl6030f6f8c336a');

}
     <caption>when I introduce a new element I need to reopen it to complete the missing information. (this) contains
     the current object
     </caption>
     *
     *  *Call the ajax with the action save , it is possible to call a function after the save by creating
     * +     * a function  named afterSaveFct(r) where r is the row in HTML to display, with a attribute ctl_pk_id which is the
     * +     * primary key and id of the row
     * +     *
     * +     * As a hidden parameter the Manage_Table:object_name must be  set
     *
     */
    this.save = function (form_id) {
        var param_form={};
        waiting_box();
        try {
            this.param['action'] = 'save';
            var form = $(form_id).serialize(true);
            param_form = json_concat(this.param,form);
            var here=this; 
          } catch (e) {
            alert(e.message);
            return false;
          }
        new Ajax.Request(this.callback, {
            parameters: param_form,
            method: "post",
            onSuccess: function (req) {
                try {
                /// Display the result of the update
                /// or add , the name of the row in the table has the
                /// if p_ctl_row does not exist it means it is a new
                /// row , otherwise an update
                var answer=here.parseXML(req);
                var new_row;

                if (answer ['status'] == 'OK') {
                    if ($(answer['ctl_row']) ) {
                        new_row=$(answer['ctl_row']);
                        $(answer['ctl_row']).update(answer['html']);
                        new_row.setAttribute("ctl_pk_id",answer['ctl_pk_id']);
                    } else {
                         new_row = new Element("tr");
                        new_row.id = answer['ctl_row'];
                        new_row.innerHTML = answer['html'];
                        new_row.setAttribute("ctl_pk_id",answer['ctl_pk_id']);
                        /**
                         *  put the element at the right place
                         */
                        here.insertRow($("tb"+answer['ctl']) , new_row,here.sort_column);
                    }
                    new Effect.Highlight(answer['ctl_row'] ,{startcolor: '#FAD4D4',endcolor: '#F78082' });
                    alternate_row_color("tb"+answer['ctl']);
                    remove_waiting_box();
                    $(here.control).hide();
                    // if there is an afterSaveFct then call it
                    if (here.afterSaveFct != undefined && typeof here.afterSaveFct  == "function") {
                        try {
                            here.afterSaveFct.call(here,new_row,req);
                        } catch (e) {
                            console.error("FAIL253 afterSaveFct ");
                            console.error(e.message);
                            console.error (here.afterSaveFct);
                        }
                    }
                    
                } else {
                    remove_waiting_box();
                    smoke.alert(content[48]);
                    $(here.control).update(answer['html']);
                   
                }
            }
            catch (e) {
                    alert(e.message);
                    return false;
                }
            }


        });
        return false;
    };
    /**
     *@brief call the ajax with action delete
     *@param id (pk) of the data row
     */
    this.remove = function (p_id, p_ctl) {
        this.param['p_id'] = p_id;
        this.param['action'] = 'delete';
        this.param['ctl'] = p_ctl;
        var here=this;
        $(p_ctl+"_"+p_id).addClassName("highlight");
        smoke.confirm(content[47],
        function (e)
        {
            if (e ) {
                new Ajax.Request(here.callback, {
                parameters: here.param,
                method: "get",
                onSuccess: function (req) {
                    var answer = here.parseXML(req);
                    if (answer['status'] == 'OK') {
                        var x=answer['ctl_row'];
                        $(x).remove();
                        alternate_row_color("tb"+answer['ctl']);
                        }else {
                             smoke.alert(answer['html']);
                        }
                    }
                }); 
            }
            else {
               $(p_ctl+"_"+p_id).removeClassName("highlight");
            }
        })   ;
    
    };
    /**
     *@brief display a dialog box with the information
     * of the data row
     *@param id (pk) of the data row
     *@param ctl name of the object 
     */
    this.input = function (p_id, p_ctl) {
        waiting_box();
        this.param['p_id'] = p_id;
        this.param['action'] = 'input';
        this.param['ctl'] = p_ctl;
        var control = this.control;
        var here = this;
         
        // display the form to enter data
        new Ajax.Request(this.callback, {
            parameters: this.param,
            method: "get",
            onSuccess: function (req) {
                
                try {
                    var x = here.parseXML(req);
                    var obj = {id: control, "cssclass": here.cssclass, "html": loading()};
                    create_div(obj);
                    var pos = calcy(50);
      
                    here.mt_style["top"]=pos+"px";
                    $(obj.id).setStyle(here.mt_style);
                    remove_waiting_box();
                    $(obj.id).update(x['html']);
                    $(obj.id).show();
                } catch (e) {
                    smoke.alert(content[48] + e.message);
                }

            }
        });
    };


}

