<div class="tab-pane" id="site_bonus" role="tabpanel">
    <div class="kt-section">
        <h3 class="kt-section__title">
            Ежедневный бонус:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>От:</label>
                <input type="text" class="form-control" placeholder="" value="{{$settings->daily_bonus_min}}" name="daily_bonus_min" />
            </div>
            <div class="col-lg-4">
                <label>До:</label>
                <input type="text" class="form-control" placeholder="" value="{{$settings->daily_bonus_max}}" name="daily_bonus_max" />
            </div>
        </div>
    </div>
    <div class="kt-section">
        <h3 class="kt-section__title">
            Ежечасный бонус:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>От:</label>
                <input type="text" class="form-control" placeholder="" value="{{$settings->hourly_bonus_min}}" name="hourly_bonus_min" />
            </div>
            <div class="col-lg-4">
                <label>До:</label>
                <input type="text" class="form-control" placeholder="" value="{{$settings->hourly_bonus_max}}" name="hourly_bonus_max" />
            </div>
        </div>
    </div>
    <div class="kt-section">
        <h3 class="kt-section__title">
            Единоразовый бонус:
        </h3>
        <div class="form-group row">
            <div class="col-lg-4">
                <label>Сумма:</label>
                <input type="text" class="form-control" placeholder="" value="{{$settings->onetime_bonus}}" name="onetime_bonus" />
            </div>
        </div>
    </div>
</div>
