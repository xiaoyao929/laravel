var Script = function () {

    $.validator.setDefaults({
        submitHandler: function() {
            alert("submitted!");
        }
    });

    $().ready(function() {
        // validate the comment form when it is submitted
        $("#commentForm").validate();

        // validate signup form on keyup and submit
        $("#signupForm").validate({
            rules: {
                firstname: "required",
                lastname: "required",
                username: {
                    required: true,
                    minlength: 2
                },
                nickname: {
                    required: true,
                    minlength: 2
                },
                account: {
                    required: true,
                    minlength: 2
                },
                password: {
                    required: true,
                    minlength: 5
                },
                confirm_password: {
                    required: true,
                    minlength: 5,
                    equalTo: "#password"
                },
                email: {
                    required: false,
                    email: true
                },
                topic: {
                    required: "#newsletter:checked",
                    minlength: 2
                },
                agree: "required"
            },
            messages: {
                firstname: "请输入 firstname",
                lastname: "请输入 lastname",
                nickname: {
                    required: "请输入昵称",
                    minlength: "昵称必须包含至少2个字符"
                },
                account: {
                    required: "请输入用户名",
                    minlength: "用户名必须包含至少2个字符"
                },
                username: {
                    required: "请输入用户名",
                    minlength: "用户名必须包含至少2个字符"
                },
                password: {
                    required: "请输入密码",
                    minlength: "密码必须包含至少5个字符"
                },
                confirm_password: {
                    required: "请提供密码",
                    minlength: "密码必须包含至少5个字符",
                    equalTo: "请输入相同的密码"
                },
                email: "请输入一个有效的电子邮件地址",
                agree: "Please accept our policy"
            }
        });

        // propose username by combining first- and lastname
        $("#username").focus(function() {
            var firstname = $("#firstname").val();
            var lastname = $("#lastname").val();
            if(firstname && lastname && !this.value) {
                this.value = firstname + "." + lastname;
            }
        });

        //code to hide topic selection, disable for demo
        var newsletter = $("#newsletter");
        // newsletter topics are optional, hide at first
        var inital = newsletter.is(":checked");
        var topics = $("#newsletter_topics")[inital ? "removeClass" : "addClass"]("gray");
        var topicInputs = topics.find("input").attr("disabled", !inital);
        // show when newsletter is checked
        newsletter.click(function() {
            topics[this.checked ? "removeClass" : "addClass"]("gray");
            topicInputs.attr("disabled", !this.checked);
        });
    });


}();