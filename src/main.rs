//! tkawen-com — Sovereign cloud for Algeria.
//! Axum 0.7 + Maud + rust-embed. AGPL-3.0-or-later.

use axum::{
    body::Body,
    http::{header, HeaderValue, StatusCode, Uri},
    response::{IntoResponse, Response},
    routing::get,
    Router,
};
use rust_embed::RustEmbed;
use std::{net::SocketAddr, time::Instant};
use tower_http::{
    compression::CompressionLayer, set_header::SetResponseHeaderLayer, trace::TraceLayer,
};

mod content;
mod schemas;
mod templates;

pub const VERSION: &str = env!("CARGO_PKG_VERSION");
const SERVER_BANNER: &str = concat!("tkawen-com/", env!("CARGO_PKG_VERSION"), " (axum/maud)");

#[derive(RustEmbed)]
#[folder = "assets/"]
struct Assets;

#[tokio::main]
async fn main() {
    tracing_subscriber::fmt()
        .with_env_filter(
            tracing_subscriber::EnvFilter::try_from_default_env()
                .unwrap_or_else(|_| "info,tower_http=info".into()),
        )
        .init();

    let app = Router::new()
        .route("/", get(home))
        .route("/healthz", get(|| async { "ok" }))
        .route("/robots.txt", get(robots))
        .route("/sitemap.xml", get(sitemap))
        .route("/llms.txt", get(llms))
        .route("/llms-full.txt", get(llms_full))
        .route("/favicon.svg", get(favicon))
        .route("/og.svg", get(og_image))
        .route("/manifest.webmanifest", get(manifest))
        .route("/service-worker.js", get(service_worker))
        .route("/a7f3d2b9e1c8h4k6m9n2p5q8r1t4v7w0.txt", get(indexnow_key))
        .route("/static/*path", get(static_file))
        .fallback(get(not_found))
        .layer(CompressionLayer::new().gzip(true).br(true))
        .layer(SetResponseHeaderLayer::overriding(
            header::SERVER,
            HeaderValue::from_static(SERVER_BANNER),
        ))
        .layer(SetResponseHeaderLayer::overriding(
            header::HeaderName::from_static("x-powered-by"),
            HeaderValue::from_static("Rust + Axum + Maud (AGPL-3.0)"),
        ))
        .layer(SetResponseHeaderLayer::overriding(
            header::HeaderName::from_static("x-content-type-options"),
            HeaderValue::from_static("nosniff"),
        ))
        .layer(SetResponseHeaderLayer::overriding(
            header::HeaderName::from_static("referrer-policy"),
            HeaderValue::from_static("strict-origin-when-cross-origin"),
        ))
        .layer(SetResponseHeaderLayer::overriding(
            header::HeaderName::from_static("strict-transport-security"),
            HeaderValue::from_static("max-age=31536000; includeSubDomains"),
        ))
        .layer(TraceLayer::new_for_http());

    let addr: SocketAddr = std::env::var("TKAWEN_ADDR")
        .unwrap_or_else(|_| "127.0.0.1:8088".to_string())
        .parse()
        .expect("TKAWEN_ADDR must be a valid SocketAddr");

    let listener = tokio::net::TcpListener::bind(addr).await.expect("bind");
    tracing::info!("tkawen-com v{} listening on http://{}", VERSION, addr);

    axum::serve(listener, app)
        .with_graceful_shutdown(shutdown_signal())
        .await
        .expect("serve");
}

async fn shutdown_signal() {
    let _ = tokio::signal::ctrl_c().await;
    tracing::info!("shutdown signal received");
}

async fn home() -> Response {
    let start = Instant::now();
    let _ = templates::page(0).into_string();
    let render_us = start.elapsed().as_micros();
    let final_body = templates::page(render_us).into_string();
    let total_us = start.elapsed().as_micros();

    let timing = format!("render;dur={}.{:03}", total_us / 1000, total_us % 1000);

    Response::builder()
        .status(StatusCode::OK)
        .header(header::CONTENT_TYPE, "text/html; charset=utf-8")
        .header("server-timing", timing)
        .header(
            "cache-control",
            "public, max-age=300, stale-while-revalidate=86400",
        )
        .header("vary", "accept-encoding, accept-language")
        .body(Body::from(final_body))
        .unwrap()
}

async fn robots() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "text/plain; charset=utf-8")],
        include_str!("../assets/robots.txt"),
    )
}

async fn sitemap() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "application/xml; charset=utf-8")],
        include_str!("../assets/sitemap.xml"),
    )
}

async fn llms() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "text/plain; charset=utf-8")],
        include_str!("../assets/llms.txt"),
    )
}

async fn llms_full() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "text/plain; charset=utf-8")],
        include_str!("../assets/llms-full.txt"),
    )
}

async fn favicon() -> impl IntoResponse {
    let svg = r##"<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#0a0e1a"/><path d="M16 22h32M32 22v22" stroke="#f59e0b" stroke-width="6" stroke-linecap="round"/></svg>"##;
    ([(header::CONTENT_TYPE, "image/svg+xml")], svg)
}

async fn og_image() -> impl IntoResponse {
    (
        [
            (header::CONTENT_TYPE, "image/svg+xml"),
            (header::CACHE_CONTROL, "public, max-age=604800, immutable"),
        ],
        include_str!("../assets/og.svg"),
    )
}

async fn manifest() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "application/manifest+json")],
        include_str!("../assets/manifest.webmanifest"),
    )
}

async fn service_worker() -> impl IntoResponse {
    (
        [
            (header::CONTENT_TYPE, "application/javascript"),
            (header::CACHE_CONTROL, "no-cache"),
        ],
        include_str!("../assets/service-worker.js"),
    )
}

async fn indexnow_key() -> impl IntoResponse {
    (
        [(header::CONTENT_TYPE, "text/plain; charset=utf-8")],
        include_str!("../assets/indexnow-key.txt"),
    )
}

async fn not_found() -> Response {
    let body = templates::not_found_page().into_string();
    Response::builder()
        .status(StatusCode::NOT_FOUND)
        .header(header::CONTENT_TYPE, "text/html; charset=utf-8")
        .body(Body::from(body))
        .unwrap()
}

async fn static_file(uri: Uri) -> Response {
    let path = uri.path().trim_start_matches("/static/");
    match Assets::get(path) {
        Some(content) => {
            let mime = mime_guess::from_path(path).first_or_octet_stream();
            Response::builder()
                .header(header::CONTENT_TYPE, mime.as_ref())
                .header(header::CACHE_CONTROL, "public, max-age=604800, immutable")
                .body(Body::from(content.data.into_owned()))
                .unwrap()
        }
        None => StatusCode::NOT_FOUND.into_response(),
    }
}
