<div class="p-0 d-flex flex-column h-100 container-fluid">

    <main style="flex-grow:1">
        <div class="customContainer mt-md-4 px-2 container">
            <form class="px-3 d-md-block w-100">
                <div class="align-items-center flex-wrap row">
                    <div style="width: 180px;" class="mt-3 flex-fill col-xxl-2 col-lg-2 col-md-4 col-sm-4"><label class="input-label-primary">Search Term / Auction ID </label>
                        <div class="input-field-container"><input name="search_term" id="search_term" type="text" placeholder="Enter Keyword or City" class=" input-primary" value=""></div>
                    </div>
                    <div style="width: 180px;" class="mt-3 flex-fill col-xxl-2 col-lg-2 col-md-4 col-sm-4">
                        <div style="display: flex; flex-direction: column; justify-content: space-between;" class="position-relative">
                            <div style="display: flex; align-items: center;">
                                <label for="region-select" class="select-label select-label-outside text-100 false">Region</label>
                            </div>
                            <div class="select-outside css-1uydztm-container">
                                <div class="ssa____control css-14cujn7">
                                    <div class="ssa____value-container ssa____value-container--has-value css-hlgwow">
                                        <select id="region-select" name="region" class="ssa____input" style="color: inherit; background: 0px center; opacity: 1; width: 100%; font: inherit; min-width: 2px; border: 0px; margin: 0px; outline: 0px; padding: 0px;">
                                            <option value="Australia" selected>Australia</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="width: 200px;" class="mt-3 flex-fill col-xxl-2 col-lg-2 col-md-4 col-sm-4">
                        <div style="display: flex; flex-direction: column; justify-content: space-between;" class="position-relative">
                            <div style="display: flex; align-items: center;">
                                <label for="state-select" class="select-label select-label-outside text-100 false">State</label>
                            </div>
                            <div class="select-outside css-1uydztm-container">
                                <div class="ssa____control css-14cujn7">
                                    <div class="ssa____value-container ssa____value-container--has-value css-hlgwow">
                                        <select id="state-select" name="state" class="ssa____input" style="color: inherit; background: 0px center; opacity: 1; width: 100%; font: inherit; min-width: 2px; border: 0px; margin: 0px; outline: 0px; padding: 0px;">
                                            <option value="New South Wales">New South Wales</option>
                                            <option value="Victoria">Victoria</option>
                                            <option value="Queensland" selected>Queensland</option>
                                            <option value="Western Australia">Western Australia</option>
                                            <option value="South Australia">South Australia</option>
                                            <option value="Tasmania">Tasmania</option>
                                            <option value="Australian Capital Territory">Australian Capital Territory</option>
                                            <option value="Northern Territory">Northern Territory</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex-fill col-xxl-1 col-lg-1 col-md-4 col-sm-4"><label class="input-label-primary">Postcode </label>
                        <div class="input-field-container"><input name="zip" type="text" placeholder="Enter Postcode" class=" input-primary  " value=""></div>
                    </div>

                    <div style="width: 100px;" class="mt-3 flex-fill col-xxl-2 col-lg-2 col-md-4 col-sm-4">
                        <div style="display: flex; flex-direction: column; justify-content: space-between;" class="position-relative">
                            <div style="display: flex; align-items: center;">
                                <label for="radius-select" class="select-label select-label-outside text-100 false">Radius</label>
                            </div>
                            <div class="select-outside css-1uydztm-container">
                                <div class="ssa____control css-14cujn7">
                                    <div class="ssa____value-container ssa____value-container--has-value css-hlgwow">
                                        <select id="radius-select" name="radius" class="ssa____input" style="color: inherit; background: 0px center; opacity: 1; width: 100%; font: inherit; min-width: 2px; border: 0px; margin: 0px; outline: 0px; padding: 0px;">
                                            <option value="15">15 Kilometres</option>
                                            <option value="30">30 Kilometres</option>
                                            <option value="50">50 Kilometres</option>
                                            <option value="100">100 Kilometres</option>
                                            <option value="200">200 Kilometres</option>
                                            <option value="500">500 Kilometres</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="align-self: end;" class="mt-3 mt-md-5 col-xxl-1 col-lg-3 col-md-4 col-sm-4"><button type="submit" style="height: 3.75rem;" class=" btn-custom-primary  btn-custom-large btn-custom  bg-orange  btn btn-primary" >Submit</button></div>
                    <div style="align-self: end;" class="mt-3 mt-md-5 col-xxl-1 col-lg-3 col-md-4 col-sm-4"><button type="button" style="height: 3.75rem; background-color: grey; color: white;" class="btn-custom-grey btn-custom-large btn-custom undefined btn btn-primary" >Clear</button></div>
                </div>
            </form>

        </div>
        <div class="customContainer mt-2 mt-md-4 px-2 container">
            <div class="align-items-center row"></div>
        </div>

        <div class="customContainer px-3 px-md-2 pt-md-3 pt-md-0 container">
            <div class="ms-auto d-flex paginate-container flex-row mt-4">

            </div>
            <div class="row auction__all__live__section__container__grid">

            </div>
            <div class="ms-auto paginate-container">
            </div>
        </div>
    </main>

</div>