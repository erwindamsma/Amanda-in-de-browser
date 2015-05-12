<div id="loginModal" class="modal fade bs-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <br>
            <div class="bs-example bs-example-tabs">
                <ul id="myTab" class="nav nav-tabs">
                    <li class="active"><a href="#signin" data-toggle="tab">Sign In</a></li>
                    <li class=""><a href="#signup" data-toggle="tab">Register</a></li>
                </ul>
            </div>
            <div class="modal-body">
                <div id="myTabContent" class="tab-content">
                    <div class="tab-pane fade active in" id="signin">
                        <form class="form-horizontal">
                            <fieldset>
                                <!-- Sign In Form -->
                                <!-- Text input-->
                                <div class="control-group">
                                    <label>Email address:</label>
                                    <div class="controls">
                                        <input type="text" class="form-control" placeholder="example@example.com">
                                    </div>
                                </div>

                                <!-- Password input-->
                                <div class="control-group">
                                    <label>Password:</label>
                                    <div class="controls">
                                        <input type="password" class="form-control"  placeholder="********">
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="control-group">
                                    <label class="control-label" for="signin"></label>
                                    <div class="controls">
                                        <button id="signin" name="signin" class="btn btn-success">Sign In</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="signup">
                        <form class="form-horizontal">
                            <fieldset>
                                <!-- Sign Up Form -->
                                <!-- Email input-->
                                <div class="control-group">
                                    <label class="control-label" for="Email">Email:</label>
                                    <div class="controls">
                                        <input id="Email" name="Email" class="form-control" type="text" placeholder="JoeSixpack@sixpacksrus.com" class="input-large" required="">
                                    </div>
                                </div>

                                <!-- Password input-->
                                <div class="control-group">
                                    <label class="control-label" for="password">Password:</label>
                                    <div class="controls">
                                        <input id="password" name="password" class="form-control" type="password" placeholder="********" class="input-large" required="">
                                        <em>1-8 Characters</em>
                                    </div>
                                </div>

                                <!-- Re-enter password input-->
                                <div class="control-group">
                                    <label class="control-label" for="reenterpassword">Re-Enter Password:</label>
                                    <div class="controls">
                                        <input id="reenterpassword" class="form-control" name="reenterpassword" type="password" placeholder="********" class="input-large" required="">
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="control-group">
                                    <label class="control-label" for="confirmsignup"></label>
                                    <div class="controls">
                                        <button id="confirmsignup" name="confirmsignup" class="btn btn-success">Sign Up</button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <center>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </center>
            </div>
        </div>
    </div>
</div>