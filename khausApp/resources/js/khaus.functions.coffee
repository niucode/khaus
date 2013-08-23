$(document).ready ->
    $.fn.khausNumberFormat = ->
        @.each ->
            replaceNumber = (number) ->
                number = number.replace /[^0-9]+/g, ""
                number = number.replace /\B(?=(\d{3})+(?!\d))/g, "."
            if $(@).is(":input")
                number = $(@).val()
                $(@).val replaceNumber(number)
            else 
                number = $(@).html()
                $(@).html replaceNumber(number)

    $.fn.khausInputMoney = ->
        @.each ->
            $(@).khausInputNumber()
            $(@).khausNumberFormat()
            $(@).on "keydown", (t) ->
                p = t.keyCode
                if p != 37 and p != 39 # arrows
                    setTimeout =>
                        $(@).khausNumberFormat()
                    , 1

    $.fn.khausInputRut = ->
        @.each ->
            $(@).khausInputNumber()
            $(@).attr "maxlength", 12
            number = $(@).val()
            number = number.replace /[^0-9kK]+/g, ""
            number = number.replace /([0-9kK])$/, "-$1"
            number = number.replace /\B(?=(\d{3})+(?!\d))/g, "."
            $(@).val number
            $(@).on "keydown", (t) ->
                p = t.keyCode
                if p != 37 and p != 39 # arrows
                    setTimeout =>
                        number = $(@).val()
                        number = number.replace /[^0-9kK]+/g, ""
                        number = number.replace /([0-9kK])$/, "-$1"
                        number = number.replace /\B(?=(\d{3})+(?!\d))/g, "."
                        $(@).val number
                    , 1

    $.fn.khausInputNumber = ->
        @.each ->
            $(@).on "keydown", (t) ->
                p = t.keyCode
                if p != 8 and p != 37 and p != 39 and p != 9 # arrows, backspace and tab
                    rex = new RegExp("[0-9]")
                    if !String.fromCharCode(t.keyCode).match(rex) and !(p >= 96 and p <= 105)
                        t.preventDefault()

    $.fn.khausForm = (settings) ->
        o = $.extend
            refresh             : true
            popoverContainer    : false
            async               : true
            send                : ->
            response            : (message, formData) ->
        , settings
        if localStorage.getItem "khausFormResponse"
            message = JSON.parse localStorage.khausFormResponse
            o.response message
            localStorage.removeItem "khausFormResponse"
        @.each ->
            form = $(@)
            if form.is "form"
                form.on "submit", (e) ->
                    o.send()
                    e.preventDefault()
                    form.find(".popover").remove()
                    formData = form.serialize()
                    $.ajax(form.attr("action"),
                        dataType    : "json"
                        type        : form.attr "method"
                        async       : o.async
                        data        : formData
                    ).done (response) ->
                        if response.khausFormResponse?
                            # si existen errores en el formulario
                            if response.khausFormResponse['form-errors']?
                                $.each response.khausFormResponse['form-errors'], (key, value) ->
                                    form.find(":input[name=#{key}]").popover("destroy").popover(
                                        trigger     : "manual"
                                        content     : value
                                        container   : o.popoverContainer
                                    ).popover "show"
                            # si existe peticion de location
                            else if response.khausFormResponse['form-location']?
                                window.location = response.khausFormResponse['form-location']
                            else
                                if o.refresh
                                    localStorage.khausFormResponse = JSON.stringify(response.khausFormResponse);
                                    location.reload()
                                else
                                    paramFormData = {}
                                    $.each form.serializeArray(), (k, v)->
                                        paramFormData[v.name] = v.value
                                    o.response response.khausFormResponse, paramFormData

    $.bootstrapAlert = (message, style = "")->
        div = $("<div>", 
            "class" : "alert alert-dismissable #{style}"
        ).html message
        $("<button>",
            "class"         : "close"
            "data-dismiss"  : "alert"
            "aria-hidden"   : "true"
        ).html("&times;").prependTo div
        return div
    return