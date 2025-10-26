<div class="tab-pane fade" id="tab-sharedFolder">
    <div class="tree">
        @foreach($sharedFolders as $key => $folders)
            <ul>
                <li>
                    <span class="label label-primary">
                        <i class="fa fa-lg fa-folder-open"></i> {{{$groupNames[$key]}}}</span>

                    <?php $result = ''; $currDepth = 0; $lastNodeIndex = count($folders) - 1; ?>

                    @foreach ($folders as $index => $folder)
                        @if ($folder['depth'] > $currDepth || $index == 0)
                            <?php $result .= '<ul>'; ?>
                        @endif

                        @if ($folder['depth'] < $currDepth)
                            <?php $result .= str_repeat('</ul></li>', $currDepth - $folder['depth']); ?>
                        @endif

                        <?php /*Always open a node*/ $t = ( $index == 0 ) ? 1 : 2?>

                        <?php
                        $fileCount = ( array_key_exists($folder['id'], $folderToCount) ) ? $folderToCount[$folder['id']] : 0;
                        $labelColorClass = ($folder['depth'] == 0) ? 'label-success' : 'label-info';
                        $result .= '<li><span class="label '.$labelColorClass.'"><i class="fa fa-lg fa-folder-open"></i>&nbsp;' . $folder['name'] . '&nbsp; <span class="badge bg-color-darken">' . $fileCount . ' Files</span> </span>';

                        if ( $folder['isShared'] )
                        {
                            $result .= ' &ndash; ' . link_to_route('projectDocument.mySharedFolder', trans('documentManagementFolders.open'), array( $project->id, $folder['id'] ));
                        }
                        ?>

                        @if ($index != $lastNodeIndex && $folders[$index + 1]['depth'] <= $folders[$index]['depth'])
                            <?php $result .= '</li>'?>
                        @endif

                        <?php $currDepth = $folder['depth']?>

                        @if ($index == $lastNodeIndex)
                            <?php $result .= '</ul>' . str_repeat('</li></ul>', $currDepth)?>
                        @endif
                    @endforeach

                    {{$result}}
                </li>
            </ul>
        @endforeach

    </div>

</div>