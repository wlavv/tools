<script>

    Dropzone.autoDiscover = true;

    (function(doc,found) {
        
      window.addEventListener('DOMSubtreeModified', function() {
    
        var element = doc.querySelector(".dz-success-mark");
    
        if(found && !element) found = false;

        if(element){
          executeFunction();
          this.removeEventListener('DOMSubtreeModified',arguments.callee,false);
          found = true;
        }
    
      }, false);
      
    })(document,false);
    
</script>