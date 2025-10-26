require 'fileutils'
require 'cgi'

BUILDSPACE_SRC_BASE_DIR  = "web/js/dojotoolkit/buildspace"
BUILDSPACE_DEST_BASE_DIR = "web/js/release/buildspace"
TWGANTT_SRC_BASE_DIR     = "web/js/Gantt"
TWGANTT_DEST_BASE_DIR    = "web/js/release/Gantt"
COMPILER                 = "google-closure-compiler"
COMPILER_FALLBACK        = "closure-compiler"
SVNAPP                   = "svn"
ECMASCRIPT               = "ECMASCRIPT_2015"

$compiler = COMPILER

if `which #$compiler`.length < 1
  puts "#$compiler doesn't exist"
  $compiler=COMPILER_FALLBACK
  puts "Using #$compiler"
end

$prog = File.basename($0)

def usage
  print "Usage: ", $prog, " #revision\n"
  exit(1)
end

begin
  raise ArgumentError if ARGV.empty?

  $revision = ARGV.shift
  
  raise ArgumentError if !Integer($revision)
rescue ArgumentError
  usage()
end

$previousRevision = $revision.to_i - 1

$javascriptFiles = []
$javascriptDirs  = []

$htmlFiles = []
$htmlDirs  = []

$cssFiles = []
$cssDirs  = []

`#{SVNAPP} diff #{BUILDSPACE_SRC_BASE_DIR} --summarize --revision #{$previousRevision}:#{$revision} `.split("\n").each do |line|
  op   = line[0,1]
  file = line[4..-1]

  # escape the filename
  file = CGI.escapeHTML(file).strip

  case op
    when 'A', 'M' then
      ext = File.extname(file)
      dir =  File.dirname(file).split(BUILDSPACE_SRC_BASE_DIR)[1]
        case ext.downcase
          when '.js' then
            $javascriptFiles << file
            $javascriptDirs << dir unless $javascriptDirs.include?(dir)
          when '.html' then
            $htmlFiles << file
            $htmlDirs << dir unless $htmlDirs.include?(dir)
          when '.css' then
            $cssFiles << file
            $cssDirs << dir unless $cssDirs.include?(dir)
        end
  end

end

# ========================================================================================================================================
# For Buildspace Javascript files
# ========================================================================================================================================
puts $javascriptDirs

$javascriptDirs.each do |dir|
  FileUtils.mkdir_p "#{BUILDSPACE_DEST_BASE_DIR}#{dir}"
end

$javascriptFiles.each do |file|
  $relativePath =  File.dirname(file).split(BUILDSPACE_SRC_BASE_DIR)[1]
  $basename = File.basename(file, ".js")
  $outputFile = "#{BUILDSPACE_DEST_BASE_DIR}#{$relativePath}/#{$basename}.js"
  puts "#$compiler --language_in=#{ECMASCRIPT} --js #{file} --js_output_file #{$outputFile}"
  `#$compiler --language_in=#{ECMASCRIPT} --js #{file} --js_output_file #{$outputFile}`
end

# ========================================================================================================================================
# For HTML Files
# ========================================================================================================================================
puts $htmlDirs

$htmlDirs.each do |dir|
  FileUtils.mkdir_p "#{BUILDSPACE_DEST_BASE_DIR}#{dir}"
end

$htmlFiles.each do |file|
  $relativePath =  File.dirname(file).split(BUILDSPACE_SRC_BASE_DIR)[1]
  $basename = File.basename(file, ".html")
  $outputFile = "#{BUILDSPACE_DEST_BASE_DIR}#{$relativePath}/#{$basename}.html"
  FileUtils.cp(file, $outputFile)
end

# ========================================================================================================================================
# For Css Files
# ========================================================================================================================================
puts $cssDirs

$cssDirs.each do |dir|
  FileUtils.mkdir_p "#{BUILDSPACE_DEST_BASE_DIR}#{dir}"
end

$cssFiles.each do |file|
  $relativePath =  File.dirname(file).split(BUILDSPACE_SRC_BASE_DIR)[1]
  $basename = File.basename(file, ".css")
  $outputFile = "#{BUILDSPACE_DEST_BASE_DIR}#{$relativePath}/#{$basename}.css"
  FileUtils.cp(file, $outputFile)
end

# ========================================================================================================================================
# For TwGantt Javascript files
# ========================================================================================================================================
$javascriptFiles = []
$javascriptDirs  = []

`#{SVNAPP} diff #{TWGANTT_SRC_BASE_DIR} --summarize --revision #{$previousRevision}:#{$revision} `.split("\n").each do |line|

  op   = line[0,1]
  file = line[4..-1]

  # escape the filename
  file = CGI.escapeHTML(file).strip

  case op
    when 'A', 'M' then
      ext = File.extname(file)
      dir =  File.dirname(file).split(TWGANTT_SRC_BASE_DIR)[1]
        case ext.downcase
          when '.js' then
            $javascriptFiles << file
            $javascriptDirs << dir unless $javascriptDirs.include?(dir)
        end
  end

end

puts $javascriptDirs

$javascriptDirs.each do |dir|
  FileUtils.mkdir_p "#{TWGANTT_DEST_BASE_DIR}#{dir}"
end

$javascriptFiles.each do |file|
  $relativePath =  File.dirname(file).split(TWGANTT_SRC_BASE_DIR)[1]
  $basename = File.basename(file, ".js")
  $outputFile = "#{TWGANTT_DEST_BASE_DIR}#{$relativePath}/#{$basename}.js"
  puts "#$compiler --language_in=#{ECMASCRIPT} --js #{file} --js_output_file #{$outputFile}"
  `#$compiler --language_in=#{ECMASCRIPT} --js #{file} --js_output_file #{$outputFile}`
end
