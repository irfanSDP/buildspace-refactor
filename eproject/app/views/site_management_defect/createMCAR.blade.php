@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.mcar') }}}</li>
    </ol>

@endsection

@section('content')


<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
             <h2>{{{ trans('siteManagementDefect.mcar-title') }}}</h2> 
        </header>

        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body no-padding">
                 {{ Form::open(array('class'=>'smart-form')) }}
                     <fieldset id="form">
                        <section>
                            <label for="project">{{{ trans('siteManagementDefect.project') }}}</label>
                            &#58;&nbsp;{{{$project->title}}}
                            <input type="hidden" name="project" class="form-control" value="{{{$project->id}}}">
                        </section>
                        <section>
                            <label for="project">{{{ trans('siteManagementDefect.mcar-number') }}}</label>
                            <input type="text" style="color:red" class="form-control" name="mcar_number" value="{{{$mcarNumber}}}">
                            {{ $errors->first('mcar_number', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label for="project">{{{ trans('siteManagementDefect.sub-con') }}}</label>
                              &#58;&nbsp;{{{$contractor->name}}}
                              <input type="hidden" class="form-control" name="sub_con" value="{{{$contractor->id}}}">
                        </section>
                        <section>
                            <label for="project">{{{ trans('siteManagementDefect.work-description') }}}</label>
                            <input type="text" class="form-control" name="work_description">
                            {{ $errors->first('work_description', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label for="remark">{{{ trans('siteManagementDefect.remark') }}}</label>
                            <textarea class="form-control" rows="5" name="remark" id="remark"></textarea>
                            {{ $errors->first('remark', '<em class="invalid">:message</em>') }}
                        </section>
                    </fieldset>
                    <footer>
                        {{ Form::submit(trans('siteManagementDefect.submit'), array('class' => 'btn btn-default', 'name' => 'Submit')) }}
                        {{ link_to_route('site-management-defect.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                    </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>


@endsection

@section('js')
    <script>

        $(document).ready(function () {

            $('input[type=submit]').on('click', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();

            });

        });

    </script>
@endsection