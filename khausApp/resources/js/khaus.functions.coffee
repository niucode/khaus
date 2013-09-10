$ = jQuery
$.fn.khausNumberFormat = ->
    @each ->
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
    @each ->
        $(@).khausInputNumber()
        $(@).khausNumberFormat()
        $(@).on "keydown", (t) ->
            p = t.keyCode
            if p != 37 and p != 39 and p != 13 # arrows and enter
                setTimeout =>
                    $(@).khausNumberFormat()
                , 1

$.fn.khausInputRut = ->
    @each ->
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
    @each ->
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
    @each ->
        form = $(@)
        # solo permite el procesamiento de elementos formulario
        if form.is "form"
            hash = form.attr("action") + form.attr("method") + form.attr("class") + form.attr("id")
            hash = btoa hash
            message = localStorage.getItem "khausFormResponse_#{hash}"
            if message?
                o.response JSON.parse message
                localStorage.removeItem "khausFormResponse_#{hash}"
            form.on "submit", (e) -> 
                e.preventDefault()
                o.send()
                form.find(".popover").remove()
                formData = form.serialize()
                $.ajax(form.attr("action"),
                    dataType    : "json"
                    type        : form.attr "method"
                    async       : o.async
                    data        : formData
                ).done (response) ->
                    if $.isPlainObject(response) and typeof response.khausFormResponse isnt "undefined"
                        resp = response.khausFormResponse
                        # si existen errores en el formulario
                        if resp['form-errors']?
                            $.each resp['form-errors'], (key, value) ->
                                form.find(":input[name=#{key}]").popover("destroy").popover(
                                    trigger     : "manual"
                                    content     : value
                                    container   : o.popoverContainer
                                ).popover "show"
                        # si existe peticion de location en el formulario
                        else if resp['form-location']?
                            window.location = resp['form-location']
                        else
                            if o.refresh
                                localStorage.setItem("khausFormResponse_#{hash}", JSON.stringify resp)
                                location.reload()
                            else
                                paramFormData = {}
                                $.each form.serializeArray(), (k, v)->
                                    paramFormData[v.name] = v.value
                                o.response resp, paramFormData
                    else
                        if o.refresh
                            localStorage.setItem("khausFormResponse_#{hash}", JSON.stringify response)
                            location.reload()
                        else
                            paramFormData = {}
                            $.each form.serializeArray(), (k, v)->
                                paramFormData[v.name] = v.value
                            o.response response, paramFormData
                            return

$.khausNotify = (content, settings) ->
    if content?
        contentData = {}
        o = $.extend
            delay : 5000
        , settings
        container = $(".khaus-notify-container")
        if container.size() == 0
            container = $("<div>", "class":"khaus-notify-container").prependTo "body"
        if $.isPlainObject content
            if content.khausFormResponse
                contentData = content.khausFormResponse
            else
                contentData = content
        else
            contentData = {success:content}
        $.each contentData, (type, message) ->
            notify = $("<div>", "class":"khaus-notify khaus-notify-#{type}")
            notify.html message
            notify.appendTo container
            notify.on "click", ->
                $(@).removeClass "khaus-notify-show"
                $(@).addClass "khaus-notify-hide"
                $(@).one "webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend", ->
                    $(@).remove()
            setTimeout ->
                notify.addClass "khaus-notify-show"
                if o.delay > 0
                    setTimeout ->
                        notify.trigger "click"
                    , o.delay
            , 1

$.khausAlert = (title, message) ->
    if $(".khaus-modal-alert").size() > 0
        $(".khaus-modal-alert").remove()
    modal_D1 = $("<div>", "class":"modal fade khaus-modal-alert")
    modal_D2 = $("<div>", "class":"modal-dialog").appendTo modal_D1
    modal_D3 = $("<div>", "class":"modal-content").appendTo modal_D2
    modal_header = $("<div>", "class":"modal-header").appendTo modal_D3
    $("<h4>", "class":"modal-title").html(title).appendTo modal_header
    modal_body = $("<div>", "class":"modal-body").html(message).appendTo modal_D3
    modal_footer = $("<div>", "class":"modal-footer").appendTo modal_D3
    $("<button>", "type":"button", "class":"btn btn-primary", "data-dismiss":"modal").html("Aceptar").appendTo modal_footer
    modal_D1.modal "show"


$.khausConfirm = (title, message, callback = ->) ->
    if $(".khaus-modal-confirm").size() > 0
        $(".khaus-modal-confirm").remove()
    modal_D1 = $("<div>", "class":"modal fade khaus-modal-confirm")
    modal_D2 = $("<div>", "class":"modal-dialog").appendTo modal_D1
    modal_D3 = $("<div>", "class":"modal-content").appendTo modal_D2
    modal_header = $("<div>", "class":"modal-header").appendTo modal_D3
    $("<h4>", "class":"modal-title").html(title).appendTo modal_header
    modal_body = $("<div>", "class":"modal-body").html(message).appendTo modal_D3
    modal_footer = $("<div>", "class":"modal-footer").appendTo modal_D3
    $("<button>", "type":"button", "class":"btn btn-default", "data-dismiss":"modal").html("Cancelar").appendTo modal_footer
    $("<button>", "type":"button", "class":"btn btn-primary", "data-dismiss":"modal")
        .html("Aceptar")
        .on "click", ->
            callback()
            return
        .appendTo modal_footer
    modal_D1.modal "show"

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