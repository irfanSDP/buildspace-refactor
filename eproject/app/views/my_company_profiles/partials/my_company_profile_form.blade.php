<article class="col-sm-12 col-md-12 col-lg-12" style="padding-top: 10px;">
	<div class="jarviswidget jarviswidget-sortable">
		<header role="heading">
			<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
			<h2>Update My Company's Profile</h2>
		</header>

		<!-- widget div-->
		<div role="content">
			<!-- widget content -->
			<div class="widget-body no-padding">
				{{ Form::model($companyProfile, array('class' => 'smart-form', 'method' => 'PUT', 'enctype' => "multipart/form-data")) }}
				<fieldset>
					<section>
						<label class="label">Company Name<span class="required">*</span>:</label>
						<label class="input {{{ $errors->has('name') ? 'state-error' : null }}}">
							{{ Form::text('name', Input::old('name'), array('required' => 'required')) }}
						</label>
						{{ $errors->first('name', '<em class="invalid">:message</em>') }}
					</section>
				</fieldset>

				<?php
				/*if($companyProfile->company_logo_path)
				{
					$logoSrc = $companyProfile->company_logo_path;
					$caption = $companyProfile->company_logo_filename;
					$borderStyle = "dotted";
				}
				else
				{
					$logoSrc = "";
					$caption = "No Company Logo";
					$borderStyle = "dashed";
				}
				?>
                <fieldset>
                    <section class="col col-8 pull-right">
                        <label class="label">Company Logo</label>
                        <input type="file" name="company_logo" id="fileToUpload">
                    </section>
                    <section class="col col-4">
                        <img style="border: 1px {{{ $borderStyle }}} black; width:200px; height: 150px;" src="{{{ $logoSrc }}}" class="logo">
                        <span class="pull-right"><strong>{{{ $caption }}}</strong></span>
                    </section>
                </fieldset>
                <?php
                */ ?>

				<footer>
					<button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('forms.save') }}</button>
				</footer>
				{{ Form::close() }}
			</div>
			<!-- end widget content -->

		</div>
		<!-- end widget div -->
	</div>
</article>