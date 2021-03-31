<?php

namespace providencecollege\AzureUpload;

class Azure_Upload_Form {
    
	/*--------------------------------------------------------------------------------------
    *
    * Add Actions
    *
    *--------------------------------------------------------------------------------------*/

	public static function init() {	
		add_action('admin_menu', array( __CLASS__, 'add_tools_page' ));

	}

	
	public static function add_tools_page(){
		add_management_page( 'Upload your Tableau Files','Upload your Tableau Files','edit_posts', 'tableau-upload', array(__CLASS__, 'render_tools_page') );
	}
		

	public static function render_tools_page() {
			$azuresas = get_theme_mod( 'opts_azure_sas' );
			
		?>

			<div id="message" style="display: none;" class="notice notice-success"><p>Upload Successful</p></div>
			
			<div class="wrap">  
			<h1>Upload your Tableau files</h1>

			<?php 
			if( $azuresas == "" || !$azuresas ){
				echo "No access";
				return;
			}
			?>
			

			<form action="/" method="put" id="upload-form">
				<input type="file" name="file" id="file" />
				<input type="submit" id="upload" value="Upload your file" />
			</form>

			<script src="<?php echo get_template_directory_uri(); ?>/core/admin/js/azure-storage-blob.min.js"></script>

			<script>

                document.querySelector("#upload-form").addEventListener( "submit", async function(e) {			
                    
                    // prevent default form submit                        			
                    e.preventDefault();		
                    
                    // may we please, azure?
                    await sendStorageRequest() 

                });
	                     

                const message = document.querySelector('#message');


				const account = {
    				name: "pctableaustorage",
    				sas: "<?php echo $azuresas; ?>",
                    container: "tableau"
				};
	
                const containerURL = new azblob.ContainerURL(
                    `https://${account.name}.blob.core.windows.net/${account.container}${account.sas}`,
                    azblob.StorageURL.newPipeline(new azblob.AnonymousCredential));                    


				async function sendStorageRequest() {

					const file = document.querySelector( "#file" ).files[0];

                    // todo - validate file type
                    if(!file.type.includes('sheet')) {
                        console.log('This is not a spreadsheet');
                        message.style.display="block";    
                        message.classList.remove('notice-success');
                        message.classList.add('notice-error');
	                    message.innerHTML = `<p>This is not a spreadsheet</p>`;
                        return;
                    }

                    try {                                    
                               
                        const blockBlobURL = azblob.BlockBlobURL.fromContainerURL(containerURL, "historical-data.xlsx");
                        const u = new URL(blockBlobURL.url);

                        azblob.uploadBrowserDataToBlockBlob(azblob.Aborter.none, file, blockBlobURL);          
                        document.querySelector('#message').style.display="block";  
                        message.classList.remove('notice-error');
                        message.classList.add('notice-success');        
	                    document.querySelector("#message").innerHTML = `<p>Upload is successful</p>
                        <p>The URL is: <strong>${u.origin}${u.pathname}</strong></p>`;

             
                    } catch (error) {                        
                        message.style.display="block";    
                        message.classList.remove('notice-success');
                        message.classList.add('notice-error');
                        message.innerHTML = `<p>Sorry, something went wrong</p>`;
                        console.log(error);
                    } 

				}
	
			</script>

		<?php
        

		
	}


}