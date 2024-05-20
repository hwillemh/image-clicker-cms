/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function ($, window, document, undefined) {
    var formType = ""
    $(document).ready(function () {
        $(".rwmb-field.event-type input").change((e) => setFormType(e.target.value))
        setFormType($(".rwmb-field.event-type input").value)
        
    });
    const setFormType = (type) => {
        console.log(type)
        if (type === undefined) {
            $(".hide-initial").css("display", "none");
        }else if (type === "ticketmaster"){
            $(".ticketmaster-only").css("display", "flex");
        }
    }

})(jQuery, window, window.document, undefined);