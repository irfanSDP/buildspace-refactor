<div class="tab-pane fade in active" id="tab-myFolder">

    <!-- Nestable start -->
    <!-- Root folder -->
    <?php
    $elementWidth = 300;
    ?>
    <div class="dd no-float" id="root-folder">
        <ol class="dd-list">
            <li class="dd-item" style="width:{{{ $elementWidth }}}px">
                <?php
                $toggleExpandIndicator = '<span class="label label-danger folder-state-label"><i class="fa fa-lg fa-folder-open folder-state"></i></span>';
                ?>
                <div class="dd3-content bg-grey-d" style="border-radius: 25px 25px 25px {{{ (count($descendants) > 0) ? 0 : '25px' }}};">{{ $toggleExpandIndicator }}&nbsp;<span class="label label-danger">{{{$root->name}}}</span>
                    @if($isEditor)
                        <div style="float:right"><a href="#" tabindex="0" data-toggle="popover" onclick="return false;" class="color-bootstrap-danger"
                                                    data-content="<div style='min-width:120px'><a href='#' onclick='createNewFolder({{{$root->id}}});return false;'>{{ trans('documentManagementFolders.newFolder') }}</a></div>">{{ trans('documentManagementFolders.options') }}</a>
                        </div>
                    @endif
                </div>
            </li>
        </ol>
    </div>
    <!-- Root folder end -->

    <!-- Sub folders -->
    <div id="folders">
        <?php
        function populate($items, $folderToCount, $depth, $project, $editable, $isEditor){
            $elementWidth = 580;
            $html = '';
            if(isset($items) && (count($items) > 0)){

                $html .= '<ol class="dd-list">';

                $colorClasses = array(
                        1 => 'bg-grey-9',
                        2 => 'bg-grey-a',
                        3 => 'bg-grey-b',
                        4 => 'bg-grey-c',
                        5 => 'bg-grey-d',
                        6 => 'bg-grey-e',
                        7 => 'bg-white'
                );

                foreach($items as $item){
                    $fileCount = ( array_key_exists($item['id'], $folderToCount) ) ? $folderToCount[$item['id']] : 0;

                    $listItemColour = $colorClasses[ ( ( $depth - 1 ) % count($colorClasses) ) + 1 ];
                    $html .= '<li class="dd-item '.$listItemColour.'" data-id="'.$item['id'].'" style="width:'.$elementWidth.'px; border-radius: 25px;">';

                    if( $editable )
                    {
                        // Handle
                        $html .= '<div class="dd-handle dd3-handle">&nbsp;</div>';
                    }

                    $isMainFolder = ( $depth == 1 ) ? true : false;
                    $subFolderClass = ( ! $isMainFolder ) ? '' : 'bg-grey-d';

                    $html .= '<div class="dd3-content toggle-expand ' . $subFolderClass . '" style="border-radius: 0 25px 25px 0;">';

                    $options = '<div class="options-menu" style="float:right">';

                    if( $isEditor )
                    {
                        $options .= '<a href="#" tabindex="0" data-toggle="popover" onclick="return false;"
                                                 data-content="<div style=\'min-width:120px\'>
                                                 <a href=\'' . route('projectDocument.myFolder', array( $project->id, $item['id'] )) . '\'>' . trans('documentManagementFolders.open') . '</a><br />
                                                 <a href=\'#\' onclick=\'createNewFolder(' . $item['id'] . ');return false;\'>' . trans('documentManagementFolders.newFolder') . '</a><br />
                                                 <a href=\'#\' onclick=\'renameFolder(' . $item['id'] . ');return false;\'>' . trans('documentManagementFolders.rename') . '</a><br />
                                                 <a href=\'#\' onclick=\'deleteFolder(' . $item['id'] . ');return false;\'>' . trans('files.delete') . '</a><br />
                                                 <a href=\'#\' onclick=\'shareFolder(' . $item['id'] . ');return false;\'>' . trans('documentManagementFolders.share') . '</a><br />
                                                 <a href=\'#\' onclick=\'sendNotifications(' . $item['id'] . ');return false;\'>' . trans('notifications.sendNotifications') . '</a><br />
                                                 </div>">' . trans('documentManagementFolders.options') . '</a>';
                    }
                    else
                    {
                        $options .= link_to_route('projectDocument.myFolder', trans('documentManagementFolders.open'), array( $project->id, $item['id'] ));
                    }

                    $options .= "</div>";

                    $folderName = $item['data']['folderName'];
                    $fullFolderName = $folderName;
                    $strLimit = 40;
                    if(strlen($folderName) > $strLimit )
                    {
                        $folderName = substr($folderName, 0, ( $strLimit - 3 )) . '...';
                    }

                    $toggleExpandIndicator = '<span class="label label-warning folder-state-label" data-id="'.$item['id'].'"><i class="fa fa-lg fa-folder-open folder-state" data-id="'.$item['id'].'"></i></span>';

                    $folderNameLabel = '<span class="label label-success" data-toggle="tooltip" data-placement="top" title="'.$fullFolderName.'">'.$folderName.'</span>';

                    if( $item['data']['shared'] )
                    {
                        $sharedFolderIndicatorTooltip = 'This folder is shared';
                        $sharedFolderIndicator = '<div class="badge bg-color-greenLight" data-toggle="tooltip" data-placement="top" title="' . $sharedFolderIndicatorTooltip . '"><i class="fa fa-xs fa-share-alt"></i></div>';
                    }
                    else
                    {
                        $sharedFolderIndicator = '';
                    }

                    $fileCountIndicator = '<span class="badge bg-light-steel-blue">' . $fileCount . ' Files</span>';

                    $html .= '<div class="dd-content">' . $toggleExpandIndicator . '&nbsp;' . $folderNameLabel . '&nbsp; ' . $fileCountIndicator . ' ' . $sharedFolderIndicator . $options . '</div>';

                    $html .= '</div>';

                    if( isset( $item['children'] ) )
                    {
                        $html .= populate($item['children'], $folderToCount, ( $depth + 1 ), $project, $editable, $isEditor);
                    }

                    $html .= '</li>';
                }

                $html .= '</ol>';
            }
            return $html;
        }
        ?>

        <div class="dd no-float" id="nestable-json">
        {{ populate($descendants, $folderToCount, 1, $project, $editable, $isEditor) }}
        <!-- Additional space to make dragging to the bottom easier (Start) -->
            <br/>
            <br/>
            <br/>
            <!-- Additional space to make dragging to the bottom easier (End) -->
        </div>
        <!-- Sub folders end -->

    </div>
    <!-- Nestable end -->
</div>